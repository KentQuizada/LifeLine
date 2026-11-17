<?php

namespace App\Support;

use App\Models\BloodRequest;
use App\Models\DonorMatch;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\LifeLineAlert;
use Throwable;

/**
 * Emergency notifications (M2.5, DEC-005, DEC-011): an in-app alert for every event, plus an email
 * when the user keeps email notifications on. A failure is logged and never stops the action that
 * caused it (Notifications invariant). The models call these after their database changes are saved.
 */
final class Alerts
{
    /** A matching run added this donor to a request. */
    public static function newMatch(DonorMatch $match): void
    {
        $request = $match->bloodRequest;
        $hospital = $request->hospital;

        self::send(
            $match->donor->user,
            ucfirst($request->urgency)." request: {$request->blood_type} blood needed",
            "{$hospital->name} needs {$request->units} unit(s) of {$request->blood_type} blood (about "
                .max(1, (int) round((float) $match->distance_km)).' km from you). Open Requests for you to accept or decline.',
            $request,
        );
    }

    /** A donor accepted: the requester can now see the donor's contact details (DEC-004). */
    public static function donorAccepted(DonorMatch $match): void
    {
        $request = $match->bloodRequest;
        $donor = $match->donor->user;

        self::send(
            $request->requester,
            'Donor match found!',
            "{$donor->first_name} {$donor->last_name} accepted your request {$request->reference_number}. "
                .'Contact info is shared on the request page.',
            $request,
        );
    }

    /**
     * After a status change: tell the requester when someone else changed it (e.g. an admin), and tell
     * donors still waiting or accepted when the request closes. donor_found is covered by donorAccepted().
     */
    public static function statusChanged(BloodRequest $request, string $newStatus, ?User $changedBy): void
    {
        if ($newStatus === 'donor_found') {
            return;
        }

        $label = BloodRequest::STATUS_LABELS[$newStatus];

        if ($changedBy?->id !== $request->requester_id) {
            self::send(
                $request->requester,
                "Request {$request->reference_number}: {$label}",
                "The status of your request {$request->reference_number} is now \"{$label}\".",
                $request,
            );
        }

        if (in_array($newStatus, BloodRequest::LOOKING_FOR_DONORS, true)) {
            return;
        }

        $request->matches()
            ->whereIn('status', ['notified', 'accepted'])
            ->with('donor.user')
            ->get()
            ->each(fn (DonorMatch $match) => self::send(
                $match->donor->user,
                "Request {$request->reference_number} is closed",
                "The {$request->blood_type} request at {$request->hospital->name} is now \"{$label}\". "
                    .'No action is needed. Thank you for helping.',
                $request,
            ));
    }

    public static function send(User $user, string $title, string $message, ?BloodRequest $request = null): void
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'blood_request_id' => $request?->id,
                'title' => mb_substr($title, 0, 150),
                'message' => mb_substr($message, 0, 500),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }

        // Users who turned notifications off get in-app alerts only (Notifications invariant).
        if (! $user->notifications_enabled || ! $user->hasVerifiedEmail()) {
            return;
        }

        try {
            $user->notify(new LifeLineAlert($title, $message));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
