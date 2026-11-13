<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donor;
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

    private function review(Request $request, Donor $donor, string $status): RedirectResponse
    {
        // Not fillable, so set explicitly. verified_by/verified_at record who reviewed and when, for both outcomes.
        $donor->forceFill([
            'verification_status' => $status,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ])->save();

        return back()->with('status', "{$donor->user->first_name} {$donor->user->last_name} is now {$status}.");
    }
}
