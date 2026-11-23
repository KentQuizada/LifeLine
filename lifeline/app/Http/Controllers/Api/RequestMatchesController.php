<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonorMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The requester's potential donors for one of their own requests, nearest first. Used by the
 * request page (public/js/matches.js). Never returns coordinates; name and mobile number only
 * for donors who accepted (DEC-004). Signed in through the login session (DEC-015).
 */
class RequestMatchesController extends Controller
{
    public function __invoke(Request $request, int $bloodRequest): JsonResponse
    {
        // Looked up through the user's own requests, so other people's ids give 404.
        $record = $request->user()->bloodRequests()->findOrFail($bloodRequest);

        $matches = $record->matches()
            ->with(['donor:id,user_id,blood_type', 'donor.user:id,first_name,last_name,mobile_number'])
            // Unknown distances (no shared location, DEC-026) after the known ones; MySQL sorts NULL first.
            ->orderByRaw('distance_km IS NULL, distance_km')
            ->get();

        return response()->json(['data' => $matches->map(fn (DonorMatch $match) => [
            'status' => $match->status,
            'status_label' => DonorMatch::STATUS_LABELS[$match->status],
            'blood_type' => $match->donor->blood_type,
            'distance_km' => $match->distance_km === null ? null : max(1, (int) round((float) $match->distance_km)),
            'donor' => $match->status === 'accepted' ? [
                'name' => $match->donor->user->first_name.' '.$match->donor->user->last_name,
                'mobile_number' => $match->donor->user->mobile_number,
                // Design D3 donor profile page (DEC-022).
                'profile_url' => route('requests.donor', [$record->id, $match->id]),
            ] : null,
        ])->all()]);
    }
}
