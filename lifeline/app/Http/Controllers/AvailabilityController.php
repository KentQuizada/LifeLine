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

        return back()->with('status', $donor->is_available
            ? 'You are now available to donate.'
            : 'You are now unavailable and will not appear in donor searches.');
    }
}
