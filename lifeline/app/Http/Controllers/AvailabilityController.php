<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** The donor's "available to donate" switch (design C1 home and E1 profile). */
class AvailabilityController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate(['is_available' => ['required', 'boolean']]);

        $donor = $request->user()->donor()->firstOrFail();
        $donor->update(['is_available' => $request->boolean('is_available')]);

        if (! $donor->is_available) {
            return back()->with('status', 'You are now unavailable and will not appear in donor searches.');
        }

        // "Available" alone does not put a donor in searches: they must also be verified and share a location.
        $message = match (true) {
            $donor->verification_status === 'pending' => 'You are marked available. You will appear in donor searches once an admin verifies your account.',
            $donor->verification_status !== 'verified' => 'You are marked available, but your donor account is not verified, so you will not appear in donor searches.',
            ! $donor->location_sharing => 'You are marked available. Turn on location sharing in your profile to appear in donor searches.',
            default => 'You are now available to donate.',
        };

        return back()->with('status', $message);
    }
}
