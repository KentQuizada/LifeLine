<?php

namespace App\Support;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\DonorMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Admin statistics (M2.6). One place for the numbers, so the dashboard cards, the charts
 * (GET /api/v1/admin/stats) and the reports always agree with the database.
 */
final class Statistics
{
    public static function summary(): array
    {
        $requestsByStatus = self::countBy(BloodRequest::query(), 'status', BloodRequest::STATUSES);
        $totalRequests = array_sum($requestsByStatus);

        return [
            'users_by_role' => self::countBy(User::query(), 'role', ['donor', 'requester', 'admin']),
            'donors_by_status' => self::countBy(Donor::query(), 'verification_status', ['pending', 'verified', 'rejected']),
            'donors_by_blood_type' => self::countBy(Donor::query(), 'blood_type', Donor::BLOOD_TYPES),
            'searchable_donors' => Donor::searchable()->count(),
            'requests_by_status' => $requestsByStatus,
            'requests_by_urgency' => self::countBy(BloodRequest::query(), 'urgency', ['critical', 'urgent']),
            'requests_by_blood_type' => self::countBy(BloodRequest::query(), 'blood_type', Donor::BLOOD_TYPES),
            'matches_by_status' => self::countBy(DonorMatch::query(), 'status', array_keys(DonorMatch::STATUS_LABELS)),
            'total_requests' => $totalRequests,
            // Share of requests that reached "fulfilled", in whole percent.
            'fulfilled_percent' => $totalRequests > 0 ? (int) round($requestsByStatus['fulfilled'] * 100 / $totalRequests) : 0,
            'requests_last_14_days' => self::requestsPerDay(14),
        ];
    }

    /** Count rows per value, with a 0 for every expected value that has no rows. */
    private static function countBy($query, string $column, array $values): array
    {
        $counts = $query->select($column, DB::raw('COUNT(*) AS total'))->groupBy($column)->pluck('total', $column);

        return collect($values)->mapWithKeys(fn (string $value) => [$value => (int) ($counts[$value] ?? 0)])->all();
    }

    /** Requests submitted per day for the last $days days, oldest first, including empty days. */
    private static function requestsPerDay(int $days): array
    {
        $from = now()->startOfDay()->subDays($days - 1);
        $counts = BloodRequest::where('created_at', '>=', $from)
            ->select(DB::raw('DATE(created_at) AS day'), DB::raw('COUNT(*) AS total'))
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(0, $days - 1))->map(function (int $offset) use ($from, $counts) {
            $day = $from->copy()->addDays($offset)->toDateString();

            return ['date' => $day, 'count' => (int) ($counts[$day] ?? 0)];
        })->all();
    }
}
