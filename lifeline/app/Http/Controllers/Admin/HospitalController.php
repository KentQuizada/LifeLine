<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Hospitals and blood banks: reference data that only admins manage (DEC-003, DEC-010). Full CRUD. */
class HospitalController extends Controller
{
    public function index(): View
    {
        return view('admin.hospitals.index', [
            'hospitals' => Hospital::withCount('bloodRequests')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.hospitals.form', ['hospital' => new Hospital(['city' => 'Butuan City', 'type' => 'hospital'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $hospital = Hospital::create($this->validated($request));

        return redirect()->route('admin.hospitals.index')->with('status', "{$hospital->name} was added.");
    }

    public function edit(Hospital $hospital): View
    {
        return view('admin.hospitals.form', ['hospital' => $hospital]);
    }

    public function update(Request $request, Hospital $hospital): RedirectResponse
    {
        $hospital->update($this->validated($request, $hospital));

        return redirect()->route('admin.hospitals.index')->with('status', "{$hospital->name} was updated.");
    }

    public function destroy(Hospital $hospital): RedirectResponse
    {
        // Requests keep their hospital (foreign key), so a hospital with requests cannot be removed.
        if ($hospital->bloodRequests()->exists()) {
            return back()->with('error', "{$hospital->name} has blood requests, so it cannot be deleted. Edit it instead.");
        }

        $hospital->delete();

        return redirect()->route('admin.hospitals.index')->with('status', "{$hospital->name} was deleted.");
    }

    private function validated(Request $request, ?Hospital $hospital = null): array
    {
        $bounds = config('lifeline.location_bounds');

        return $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('hospitals', 'name')->ignore($hospital?->id)],
            'type' => ['required', Rule::in(['hospital', 'blood_bank'])],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:'.implode(',', $bounds['latitude'])],
            'longitude' => ['required', 'numeric', 'between:'.implode(',', $bounds['longitude'])],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ], [
            'latitude.between' => 'Enter a latitude in the Philippines.',
            'longitude.between' => 'Enter a longitude in the Philippines.',
        ]);
    }
}
