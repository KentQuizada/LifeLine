<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use App\Support\Alerts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admins review every new donor (no ID upload, DEC-009). Only verified donors are matched (M2.3). */
class DonorVerificationController extends Controller
{
    public function index(): View
    {
        $donors = Donor::with('user')
            ->where('verification_status', 'pending')
            ->oldest()
            ->paginate(20);

        return view('admin.donors.pending', ['donors' => $donors]);
    }

    public function verify(Request $request, Donor $donor): RedirectResponse
    {
        return $this->review($request, $donor, 'verified');
    }

    public function reject(Request $request, Donor $donor): RedirectResponse
    {
        return $this->review($request, $donor, 'rejected');
    }

    /** A verified donor stops being matched and cannot accept requests (DEC-022). */
    public function suspend(Request $request, Donor $donor): RedirectResponse
    {
        return $this->change($request, $donor, 'verified', 'rejected', 'Your donor account was suspended',
            'An administrator suspended your donor account, so you will not receive new blood requests. Contact LifeLine if you think this is a mistake.');
    }

    /** A suspended or rejected donor is verified again. */
    public function reinstate(Request $request, Donor $donor): RedirectResponse
    {
        return $this->change($request, $donor, 'rejected', 'verified', 'Your donor account is verified',
            'An administrator verified your donor account. You can now be matched with blood requests.');
    }

    private function change(Request $request, Donor $donor, string $from, string $to, string $title, string $message): RedirectResponse
    {
        $name = "{$donor->user->first_name} {$donor->user->last_name}";

        if ($donor->verification_status !== $from) {
            return back()->with('error', "{$name} is {$donor->verification_status}, so this cannot be done.");
        }

        $donor->forceFill([
            'verification_status' => $to,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ])->save();

        Alerts::send($donor->user, $title, $message);

        return back()->with('status', $to === 'verified' ? "{$name} is verified again." : "{$name} is suspended.");
    }

    private function review(Request $request, Donor $donor, string $status): RedirectResponse
    {
        // Only new donors are reviewed here; a verified donor cannot be flipped to rejected (or back) by a stale page.
        if ($donor->verification_status !== 'pending') {
            return back()->with('error', "{$donor->user->first_name} {$donor->user->last_name} was already reviewed ({$donor->verification_status}).");
        }

        // Not fillable, so set explicitly. verified_by/verified_at record who reviewed and when, for both outcomes.
        $donor->forceFill([
            'verification_status' => $status,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ])->save();

        return back()->with('status', "{$donor->user->first_name} {$donor->user->last_name} is now {$status}.");
    }
}
