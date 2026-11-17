<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\Donor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Admin request management (M2.6): search, view, and the status override. Every override goes through
 * BloodRequest::changeStatus(), so request_history records the acting admin (AdminDashboard invariant),
 * and the requester is notified (M2.5).
 */
class RequestController extends Controller
{
    /** donor_found is set only by a donor's acceptance (M2.3). */
    public const OVERRIDE_STATUSES = ['fulfilled', 'cancelled', 'expired'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(BloodRequest::STATUSES)],
            'blood_type' => ['nullable', Rule::in(Donor::BLOOD_TYPES)],
            'urgency' => ['nullable', Rule::in(['critical', 'urgent'])],
        ]);

        $requests = BloodRequest::with(['hospital', 'requester'])
            ->withCount('matches')
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where(fn ($match) => $match
                ->where('reference_number', 'like', '%'.strtoupper($search).'%')
                ->orWhere('patient_name', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['blood_type'] ?? null, fn ($query, $type) => $query->where('blood_type', $type))
            ->when($filters['urgency'] ?? null, fn ($query, $urgency) => $query->where('urgency', $urgency))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.requests.index', [
            'bloodRequests' => $requests,
            'filters' => $filters,
            'bloodTypes' => Donor::BLOOD_TYPES,
        ]);
    }

    public function show(BloodRequest $bloodRequest): View
    {
        $bloodRequest->load([
            'hospital',
            'requester',
            'history' => fn ($query) => $query->with('changedBy')->orderBy('id'),
            'matches' => fn ($query) => $query->with('donor.user')->orderBy('distance_km'),
        ]);

        return view('admin.requests.show', [
            'bloodRequest' => $bloodRequest,
            'overrideStatuses' => array_values(array_filter(
                self::OVERRIDE_STATUSES,
                fn (string $status) => $bloodRequest->canChangeTo($status)
            )),
        ]);
    }

    public function updateStatus(Request $request, BloodRequest $bloodRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::OVERRIDE_STATUSES)],
            'remarks' => ['required', 'string', 'max:200'],
        ]);

        try {
            $bloodRequest->changeStatus($data['status'], $request->user(), 'Admin: '.$data['remarks']);
        } catch (InvalidArgumentException) {
            return back()->with('error', 'This request can no longer be changed to that status.');
        }

        return redirect()->route('admin.requests.show', $bloodRequest)
            ->with('status', "Request {$bloodRequest->reference_number} is now ".BloodRequest::STATUS_LABELS[$data['status']].'.');
    }
}
