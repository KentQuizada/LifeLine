<?php

namespace App\Models;

use App\Support\Alerts;
use App\Support\Distance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class BloodRequest extends Model
{
    use HasFactory;

    public const STATUSES = ['open', 'donor_found', 'fulfilled', 'cancelled', 'expired'];

    public const STATUS_LABELS = [
        'open' => 'Looking for donors',
        'donor_found' => 'Donor found',
        'fulfilled' => 'Fulfilled',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ];

    /**
     * Allowed status changes. fulfilled, cancelled and expired are final.
     * donor_found is set by matching (M2.3); expired by the admin override (M2.6).
     */
    public const TRANSITIONS = [
        'open' => ['donor_found', 'fulfilled', 'cancelled', 'expired'],
        'donor_found' => ['fulfilled', 'cancelled', 'expired'],
        'fulfilled' => [],
        'cancelled' => [],
        'expired' => [],
    ];

    /** Statuses in which a request still looks for donors: matching runs and donors may answer. */
    public const LOOKING_FOR_DONORS = ['open', 'donor_found'];

    /**
     * Red-cell (whole blood) ABO/Rh compatibility: recipient type => donor types it may receive.
     * LifeLine only suggests potential donors; professionals confirm compatibility (DEC-007).
     */
    public const COMPATIBLE_DONORS = [
        'O-' => ['O-'],
        'O+' => ['O+', 'O-'],
        'A-' => ['A-', 'O-'],
        'A+' => ['A+', 'A-', 'O+', 'O-'],
        'B-' => ['B-', 'O-'],
        'B+' => ['B+', 'B-', 'O+', 'O-'],
        'AB-' => ['AB-', 'A-', 'B-', 'O-'],
        'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-'],
    ];

    private const REFERENCE_ATTEMPTS = 5;

    // reference_number, requester_id and status are set only by submit() and changeStatus().
    protected $fillable = [
        'hospital_id',
        'patient_name',
        'blood_type',
        'units',
        'urgency',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'integer',
        ];
    }

    /** Format LL-YYMMDD-XXXXXX. Uniqueness is enforced by the database (DEC-006). */
    public static function generateReferenceNumber(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $suffix = '';

        for ($i = 0; $i < 6; $i++) {
            $suffix .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return 'LL-'.now()->format('ymd').'-'.$suffix;
    }

    /**
     * The only way to create a request: unique reference number (DEC-006), status open,
     * and the first request_history row, saved together.
     */
    public static function submit(User $requester, array $details, ?callable $generateReference = null): self
    {
        $generateReference ??= fn (): string => self::generateReferenceNumber();

        for ($attempt = 1; $attempt <= self::REFERENCE_ATTEMPTS; $attempt++) {
            $reference = $generateReference();

            // Check first (DEC-006); the UNIQUE index still catches a request saved in between.
            if (self::where('reference_number', $reference)->exists()) {
                continue;
            }

            try {
                return DB::transaction(function () use ($requester, $details, $reference) {
                    $bloodRequest = new self($details);
                    $bloodRequest->reference_number = $reference;
                    $bloodRequest->requester_id = $requester->id;
                    $bloodRequest->status = 'open';
                    $bloodRequest->save();

                    $bloodRequest->history()->create([
                        'changed_by' => $requester->id,
                        'old_status' => null,
                        'new_status' => 'open',
                        'remarks' => 'Request submitted',
                    ]);

                    return $bloodRequest;
                });
            } catch (UniqueConstraintViolationException) {
                // Another request took this reference after the check. Try a new one.
            }
        }

        throw new RuntimeException('Could not generate a unique reference number. Please try again.');
    }

    public function canChangeTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /**
     * The only way to change a status: checks TRANSITIONS and writes request_history
     * in the same transaction. Nothing on this model changes unless the change is allowed.
     */
    public function changeStatus(string $status, ?User $changedBy, ?string $remarks = null): void
    {
        DB::transaction(function () use ($status, $changedBy, $remarks) {
            // Read the saved status under a row lock, so two changes at the same moment cannot both pass.
            $oldStatus = self::whereKey($this->id)->lockForUpdate()->value('status');

            if (! in_array($status, self::TRANSITIONS[$oldStatus] ?? [], true)) {
                throw new InvalidArgumentException("A {$oldStatus} request cannot become {$status}.");
            }

            $this->status = $status;
            $this->save();

            $this->history()->create([
                'changed_by' => $changedBy?->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'remarks' => $remarks,
            ]);

            // M2.5: notify once the change is saved (runs right away when there is no transaction).
            DB::afterCommit(fn () => Alerts::statusChanged($this, $status, $changedBy));
        });
    }

    /**
     * Rule-based matching (DEC-016): adds a "notified" match for each of the nearest eligible donors
     * this request has not matched yet, ranked by distance from the hospital (DEC-014).
     * Returns how many matches were added. Closed requests are never matched.
     */
    public function matchDonors(): int
    {
        if (! in_array($this->status, self::LOOKING_FOR_DONORS, true)) {
            return 0;
        }

        $hospital = $this->hospital;

        // Only the columns ranking needs (DEC-004).
        $nearest = Donor::eligibleFor($this)
            ->orderBy('id')
            ->get(['id', 'approx_latitude', 'approx_longitude'])
            ->map(fn (Donor $donor) => [
                'donor_id' => $donor->id,
                'distance_km' => round(Distance::km(
                    (float) $hospital->latitude,
                    (float) $hospital->longitude,
                    (float) $donor->approx_latitude,
                    (float) $donor->approx_longitude,
                ), 2),
            ])
            ->sortBy('distance_km')
            ->take(config('lifeline.match_limit'));

        $added = 0;

        foreach ($nearest as $candidate) {
            // The UNIQUE (request, donor) index is the final guard if two runs overlap.
            $match = $this->matches()->firstOrCreate(
                ['donor_id' => $candidate['donor_id']],
                ['distance_km' => $candidate['distance_km']]
            );
            if ($match->wasRecentlyCreated) {
                $added++;
                // M2.5: tell the donor about the request (in-app, plus email if they keep it on).
                DB::afterCommit(fn () => Alerts::newMatch($match));
            }
        }

        return $added;
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(RequestHistory::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DonorMatch::class);
    }
}
