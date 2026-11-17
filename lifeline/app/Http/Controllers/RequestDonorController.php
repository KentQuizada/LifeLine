<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Models\DonorMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Design D3: the profile of a donor who accepted one of the requester's requests, and recording that
 * donor's donation (DEC-022). Only accepted donors are shown, so contact details stay private until the
 * donor agrees (DEC-004). Never shows coordinates, birthdate or weight.
 */
class RequestDonorController extends Controller
{
    public function show(Request $request, int $bloodRequest, int $donorMatch): View
    {
        $match = $this->acceptedMatch($request, $bloodRequest, $donorMatch);
        $donor = $match->donor;

        return view('requests.donor', [
            'match' => $match,
            'bloodRequest' => $match->bloodRequest,
            'donor' => $donor,
            'donationCount' => $donor->donations()->count(),
            'lastDonation' => $donor->lastDonationOn(),
            'donation' => $match->donation()->first(),
            'canRecord' => in_array($match->bloodRequest->status, DonorMatch::DONATION_REQUEST_STATUSES, true),
        ]);
    }

    public function storeDonation(Request $request, int $bloodRequest, int $donorMatch): RedirectResponse
    {
        $match = $this->acceptedMatch($request, $bloodRequest, $donorMatch);

        $data = $request->validate([
            'donated_on' => ['required', 'date', 'before_or_equal:today',
                'after_or_equal:'.$match->bloodRequest->created_at->toDateString()],
        ], [
            'donated_on.after_or_equal' => 'The donation date cannot be before the request was made.',
            'donated_on.before_or_equal' => 'The donation date cannot be in the future.',
        ]);

        try {
            $match->recordDonation($data['donated_on']);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('requests.donor', [$bloodRequest, $donorMatch])
            ->with('status', 'Donation recorded. Thank you for using LifeLine.');
    }

    /** Through the requester's own requests, so other people's ids give 404; only accepted donors. */
    private function acceptedMatch(Request $request, int $bloodRequest, int $donorMatch): DonorMatch
    {
        /** @var BloodRequest $record */
        $record = $request->user()->bloodRequests()->with('hospital')->findOrFail($bloodRequest);

        $match = $record->matches()
            ->where('status', 'accepted')
            ->with(['donor.user'])
            ->findOrFail($donorMatch);
        $match->setRelation('bloodRequest', $record);

        return $match;
    }
}
