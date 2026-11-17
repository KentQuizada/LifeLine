<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\Donor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reports (M2.6): a summary for a date range and CSV exports that open in Excel.
 * Exports leave out contact details; donors are listed without names (data minimisation, DEC-004).
 */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $requests = BloodRequest::whereBetween('blood_requests.created_at', [$from, $to]);

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'total' => (clone $requests)->count(),
            'byStatus' => (clone $requests)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status'),
            'byBloodType' => (clone $requests)->selectRaw('blood_type, COUNT(*) AS total')->groupBy('blood_type')->pluck('total', 'blood_type'),
            'byHospital' => (clone $requests)->join('hospitals', 'hospitals.id', '=', 'blood_requests.hospital_id')
                ->selectRaw('hospitals.name AS name, COUNT(*) AS total')->groupBy('hospitals.name')->orderByDesc('total')->pluck('total', 'name'),
            'bloodTypes' => Donor::BLOOD_TYPES,
        ]);
    }

    public function requests(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $rows = BloodRequest::with('hospital:id,name')
            ->withCount(['matches', 'matches as accepted_count' => fn ($query) => $query->where('status', 'accepted')])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('id');

        return $this->csv("lifeline-requests-{$from->toDateString()}-to-{$to->toDateString()}.csv",
            ['Reference number', 'Submitted', 'Status', 'Urgency', 'Blood type', 'Units', 'Hospital', 'Potential donors', 'Accepted'],
            function ($write) use ($rows) {
                $rows->chunk(200, fn ($chunk) => $chunk->each(fn (BloodRequest $row) => $write([
                    $row->reference_number,
                    $row->created_at->format('Y-m-d H:i'),
                    BloodRequest::STATUS_LABELS[$row->status],
                    ucfirst($row->urgency),
                    $row->blood_type,
                    $row->units,
                    $row->hospital->name,
                    $row->matches_count,
                    $row->accepted_count,
                ])));
            });
    }

    public function donors(): StreamedResponse
    {
        $rows = Donor::withCount('donations')->orderBy('id');

        return $this->csv('lifeline-donors-'.now()->toDateString().'.csv',
            ['Donor no.', 'Blood type', 'City', 'Verification', 'Available', 'Sharing location', 'Donations recorded', 'Registered'],
            function ($write) use ($rows) {
                $rows->chunk(200, fn ($chunk) => $chunk->each(fn (Donor $row) => $write([
                    $row->id,
                    $row->blood_type,
                    $row->city,
                    ucfirst($row->verification_status),
                    $row->is_available ? 'Yes' : 'No',
                    $row->location_sharing ? 'Yes' : 'No',
                    $row->donations_count,
                    $row->created_at->format('Y-m-d'),
                ])));
            });
    }

    /** Date range from ?from=&to= (defaults: the last 30 days), whole days in Philippine time (DEC-012). */
    private function range(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $to = isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : now()->endOfDay();
        $from = isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : $to->copy()->subDays(29)->startOfDay();

        return [$from, $to];
    }

    private function csv(string $filename, array $header, callable $body): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $body) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows accents correctly
            // Cells starting with = + - @ are prefixed so spreadsheets do not run them as formulas.
            $write = fn (array $row) => fputcsv($out, array_map(
                fn ($cell) => is_string($cell) && preg_match('/^[=+\-@]/', $cell) ? "'".$cell : $cell,
                $row
            ));
            $write($header);
            $body($write);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
