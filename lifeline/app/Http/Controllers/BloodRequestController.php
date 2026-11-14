<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

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

        // Rule-based matching right away (DEC-016). M2.5 notifies the matched donors.
        $bloodRequest->matchDonors();

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

    public function index(Request $request): View
    {
        return view('requests.index', [
            'bloodRequests' => $request->user()->bloodRequests()->with('hospital')->orderByDesc('id')->get(),
        ]);
    }

    /** The requester may cancel a request or mark it fulfilled. Other statuses come from M2.3 and M2.6. */
    public function updateStatus(Request $request, int $bloodRequest): RedirectResponse
    {
        // Ownership first, so other people's ids give 404 whatever the input.
        $record = $request->user()->bloodRequests()->findOrFail($bloodRequest);

        $status = $request->validate([
            'status' => ['required', Rule::in(['cancelled', 'fulfilled'])],
        ])['status'];

        try {
            $record->changeStatus(
                $status,
                $request->user(),
                $status === 'cancelled' ? 'Cancelled by requester' : 'Marked fulfilled by requester'
            );
        } catch (InvalidArgumentException) {
            return back()->with('error', 'This request can no longer be changed.');
        }

        return redirect()->route('requests.show', $record->id)->with('status', $status === 'cancelled'
            ? 'Your request was cancelled.'
            : 'Your request was marked fulfilled.');
    }

    /** "Find more donors": runs matching again for donors who became eligible since (DEC-016). */
    public function findDonors(Request $request, int $bloodRequest): RedirectResponse
    {
        $record = $request->user()->bloodRequests()->findOrFail($bloodRequest);

        if (! in_array($record->status, BloodRequest::LOOKING_FOR_DONORS, true)) {
            return back()->with('error', 'This request is no longer looking for donors.');
        }

        $added = $record->matchDonors();

        return redirect()->route('requests.show', $record->id)->with('status', $added > 0
            ? "Found {$added} more potential donor(s)."
            : 'No new potential donors right now. Try again later.');
    }
}
