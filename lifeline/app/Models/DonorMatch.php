<?php

namespace App\Models;

use App\Support\Alerts;
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
    ];

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

    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }
}
