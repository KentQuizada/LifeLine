<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/** Design E1 "My profile" for donors and requesters. Acts only on the signed-in user. */
class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $donor = $user->donor;

        return view('profile.show', [
            'user' => $user,
            'donor' => $donor,
            'donationCount' => $donor?->donations()->count() ?? 0,
            'lastDonation' => $donor?->lastDonationOn(),
        ]);
    }
}
