<?php

namespace App\Models;

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
     * in the same transaction.
     */
    public function changeStatus(string $status, ?User $changedBy, ?string $remarks = null): void
    {
        DB::transaction(function () use ($status, $changedBy, $remarks) {
            // Re-read the status under a row lock, so two changes at the same moment cannot both pass.
            $this->status = self::whereKey($this->id)->lockForUpdate()->value('status');

            if (! $this->canChangeTo($status)) {
                throw new InvalidArgumentException("A {$this->status} request cannot become {$status}.");
            }

            $oldStatus = $this->status;
            $this->status = $status;
            $this->save();

            $this->history()->create([
                'changed_by' => $changedBy?->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'remarks' => $remarks,
            ]);
        });
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
