<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Emergency blood requests made by a signed-in donor or requester (design D1, D2). */
class BloodRequestController extends Controller
{
    public function create(): View
    {
        return view('requests.create', [
            'hospitals' => Hospital::orderBy('name')->get(['id', 'name']),
            'bloodTypes' => Donor::BLOOD_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $details = $request->validate([
            'patient_name' => ['required', 'string', 'max:150'],
            'blood_type' => ['required', Rule::in(Donor::BLOOD_TYPES)],
            'units' => ['required', 'integer', 'between:1,10'],
            'urgency' => ['required', Rule::in(['critical', 'urgent'])],
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $bloodRequest = BloodRequest::submit($request->user(), $details);

        return redirect()->route('requests.show', $bloodRequest->id)->with('submitted', true);
    }

    public function show(Request $request, int $bloodRequest): View
    {
        // Looked up through the user's own requests, so other people's ids give 404.
        $record = $request->user()->bloodRequests()
            ->with(['hospital', 'history' => fn ($query) => $query->orderBy('id')])
            ->findOrFail($bloodRequest);

        return view('requests.show', ['bloodRequest' => $record]);
    }
}
