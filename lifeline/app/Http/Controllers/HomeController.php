<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // One login form for every role; admins land in their own area.
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $donor = $user->donor;

        return view('home', [
            'user' => $user,
            'donor' => $donor,
            'donationCount' => $donor?->donations()->count() ?? 0,
            'lastDonation' => $donor?->lastDonationOn(),
            'waitingMatches' => $donor?->matches()
                ->where('status', 'notified')
                ->whereHas('bloodRequest', fn ($query) => $query->whereIn('status', BloodRequest::LOOKING_FOR_DONORS))
                ->count() ?? 0,
        ]);
    }
}
