<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** A donor's own donation history. Donors may add and remove self-reported records only. */
class DonationController extends Controller
{
    public function index(Request $request): View
    {
        $donor = $request->user()->donor()->firstOrFail();

        return view('donations.index', [
            'donations' => $donor->donations()->with('hospital')->orderByDesc('donated_on')->get(),
            'hospitals' => Hospital::orderBy('name')->get(['id', 'name']),
            'birthdate' => $donor->birthdate,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donor = $request->user()->donor()->firstOrFail();

        $data = $request->validate([
            'donated_on' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after:'.$donor->birthdate->toDateString(),
                Rule::unique('donations')->where('donor_id', $donor->id),
            ],
            'hospital_id' => ['nullable', 'integer', 'exists:hospitals,id'],
        ], [
            'donated_on.after' => 'The donation date must be after your birthdate.',
            'donated_on.unique' => 'You already recorded a donation on this date.',
        ]);

        $donor->donations()->create($data + ['is_self_reported' => true]);

        return redirect()->route('donations.index')->with('status', 'Donation added to your history.');
    }

    public function destroy(Request $request, int $donation): RedirectResponse
    {
        $donor = $request->user()->donor()->firstOrFail();

        // Looked up through the donor's own records, so other donors' ids give 404.
        $record = $donor->donations()->findOrFail($donation);

        // Donations recorded through a LifeLine request (M2.3) are part of the request's history.
        abort_unless($record->is_self_reported, 403);

        $record->delete();

        return redirect()->route('donations.index')->with('status', 'Donation removed from your history.');
    }
}
