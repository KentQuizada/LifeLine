<?php

namespace App\Models;

use App\Support\Alerts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DonorMatch extends Model
{
    public const ANSWERS = ['accepted', 'declined'];

    public const STATUS_LABELS = [
        'notified' => 'Waiting for reply',
        'accepted' => 'Accepted',
        'declined' => 'Declined',
        'withdrawn' => 'Withdrawn',
    ];

    /** Request statuses in which an accepted donor's donation can be recorded (DEC-022). */
    public const DONATION_REQUEST_STATUSES = ['donor_found', 'fulfilled'];

    // status and responded_at change only through respond().
    protected $fillable = ['blood_request_id', 'donor_id', 'distance_km'];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'responded_at' => 'datetime',
        ];
    }

    /**
     * The donor's one-time answer (DEC-016). The first acceptance moves the request from open to
     * donor_found through changeStatus(), which writes the history row. Throws
     * InvalidArgumentException (with a message for the donor) when the answer is not allowed.
     */
    public function respond(string $answer): void
    {
        if (! in_array($answer, self::ANSWERS, true)) {
            throw new InvalidArgumentException('Unknown answer.');
        }

        DB::transaction(function () use ($answer) {
            // Lock the request first, then this match (always in this order), so a cancel or a
            // second click cannot slip in between the checks and the save.
            $requestStatus = BloodRequest::whereKey($this->blood_request_id)->lockForUpdate()->value('status');
            $matchStatus = self::whereKey($this->id)->lockForUpdate()->value('status');

            if ($matchStatus !== 'notified') {
                throw new InvalidArgumentException('You already answered this request.');
            }

            if (! in_array($requestStatus, BloodRequest::LOOKING_FOR_DONORS, true)) {
                throw new InvalidArgumentException('This request is no longer looking for donors.');
            }

            // A donor whose verification was withdrawn after matching cannot share contact details.
            if ($answer === 'accepted' && $this->donor()->value('verification_status') !== 'verified') {
                throw new InvalidArgumentException('Only verified donors can accept requests.');
            }

            $this->status = $answer;
            $this->responded_at = now();
            $this->save();

            if ($answer === 'accepted' && $requestStatus === 'open') {
                $this->bloodRequest->changeStatus('donor_found', $this->donor->user, 'A donor accepted');
            }

            if ($answer === 'accepted') {
                // M2.5: the requester learns that contact details are now shared (DEC-004).
                DB::afterCommit(fn () => Alerts::donorAccepted($this));
            }
        });
    }

    /**
     * A donor who accepted cannot make it after all (DEC-022). Allowed while the request still looks for
     * donors and before a donation is recorded. When no other donor has accepted, the request goes back
     * to "open" so matching and other donors continue. Throws InvalidArgumentException with a message.
     */
    public function withdraw(): void
    {
        DB::transaction(function () {
            // Same lock order as respond(): request first, then this match.
            $requestStatus = BloodRequest::whereKey($this->blood_request_id)->lockForUpdate()->value('status');
            $matchStatus = self::whereKey($this->id)->lockForUpdate()->value('status');

            if ($matchStatus !== 'accepted') {
                throw new InvalidArgumentException('Only an accepted request can be withdrawn.');
            }

            if (! in_array($requestStatus, BloodRequest::LOOKING_FOR_DONORS, true)) {
                throw new InvalidArgumentException('This request is already closed.');
            }

            if ($this->donation()->exists()) {
                throw new InvalidArgumentException('Your donation for this request is already recorded.');
            }

            $this->status = 'withdrawn';
            $this->responded_at = now();
            $this->save();

            $othersAccepted = self::where('blood_request_id', $this->blood_request_id)
                ->where('status', 'accepted')
                ->exists();

            if ($requestStatus === 'donor_found' && ! $othersAccepted) {
                $this->bloodRequest->reopen($this->donor->user, 'The donor withdrew');
            }

            DB::afterCommit(fn () => Alerts::donorWithdrew($this));
        });
    }

    /**
     * The requester records that this accepted donor gave blood for the request (DEC-022). The donation
     * goes into the donor's history (not self-reported), so the donation interval applies to matching.
     */
    public function recordDonation(string $donatedOn): Donation
    {
        return DB::transaction(function () use ($donatedOn) {
            $requestStatus = BloodRequest::whereKey($this->blood_request_id)->lockForUpdate()->value('status');
            $matchStatus = self::whereKey($this->id)->lockForUpdate()->value('status');

            if ($matchStatus !== 'accepted' || ! in_array($requestStatus, self::DONATION_REQUEST_STATUSES, true)) {
                throw new InvalidArgumentException('A donation can only be recorded for a donor who accepted.');
            }

            if ($this->donation()->exists()) {
                throw new InvalidArgumentException('This donation is already recorded.');
            }

            if (Donation::where('donor_id', $this->donor_id)->whereDate('donated_on', $donatedOn)->exists()) {
                throw new InvalidArgumentException('This donor already has a donation on that date.');
            }

            $donation = Donation::create([
                'donor_id' => $this->donor_id,
                'blood_request_id' => $this->blood_request_id,
                'hospital_id' => $this->bloodRequest->hospital_id,
                'donated_on' => $donatedOn,
                'is_self_reported' => false,
            ]);

            DB::afterCommit(fn () => Alerts::donationRecorded($this));

            return $donation;
        });
    }

    /** The donation recorded through this match, if any (same donor and request). */
    public function donation(): Builder
    {
        return Donation::where('donor_id', $this->donor_id)->where('blood_request_id', $this->blood_request_id);
    }

    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }
}
