<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * Public tracking by reference number (DEC-006, DEC-014). Shows the status only: never the
 * patient name, units, notes, remarks, requester or contact details. Used by the /track page.
 */
class TrackRequestController extends Controller
{
    public function __invoke(string $reference): JsonResponse
    {
        $reference = strtoupper(trim($reference));

        Validator::make(
            ['reference' => $reference],
            ['reference' => ['regex:/^LL-\d{6}-[A-HJ-NP-Z2-9]{6}$/']],
            ['reference.regex' => 'The reference number must look like LL-261114-ABC234.']
        )->validate();

        $bloodRequest = BloodRequest::with(['hospital:id,name', 'history' => fn ($query) => $query->orderBy('id')])
            ->where('reference_number', $reference)
            ->first();

        abort_if($bloodRequest === null, 404, 'No request found with that reference number.');

        return response()->json(['data' => [
            'reference_number' => $bloodRequest->reference_number,
            'status' => $bloodRequest->status,
            'status_label' => BloodRequest::STATUS_LABELS[$bloodRequest->status],
            'urgency' => $bloodRequest->urgency,
            'blood_type' => $bloodRequest->blood_type,
            'hospital' => $bloodRequest->hospital->name,
            'submitted_at' => $bloodRequest->created_at->toIso8601String(),
            'updated_at' => $bloodRequest->updated_at->toIso8601String(),
            'history' => $bloodRequest->history->map(fn ($entry) => [
                'status' => $entry->new_status,
                'status_label' => BloodRequest::STATUS_LABELS[$entry->new_status],
                'at' => $entry->created_at->toIso8601String(),
            ])->all(),
        ]]);
    }
}
