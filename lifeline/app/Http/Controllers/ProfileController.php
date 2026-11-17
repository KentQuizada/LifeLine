<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Support\MobileNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Design E1 "My profile" for donors and requesters. Acts only on the signed-in user. */
class ProfileController extends Controller
{
    private const MESSAGES = [
        'mobile_number.regex' => 'Enter a Philippine mobile number, for example 0917 123 4567.',
        'approx_latitude.between' => 'Choose a location in the Philippines.',
        'approx_longitude.between' => 'Choose a location in the Philippines.',
    ];

    public function show(Request $request): View
    {
        $user = $request->user();
        $donor = $user->donor;

        return view('profile.show', [
            'user' => $user,
            'donor' => $donor,
            'donationCount' => $donor?->donations()->count() ?? 0,
            'lastDonation' => $donor?->lastDonationOn(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'donor' => $request->user()->donor,
            'bloodTypes' => Donor::BLOOD_TYPES,
            'mapCenter' => config('lifeline.map.center'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $donor = $user->donor;

        // Only strings are normalized; anything else is left for validation to reject.
        if (is_string($request->input('mobile_number'))) {
            $request->merge(['mobile_number' => MobileNumber::normalize($request->input('mobile_number')) ?? $request->input('mobile_number')]);
        }

        // Email is not editable here (no re-verification flow). Role and verification are never taken from input.
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile_number' => ['required', 'string', 'regex:/^09\d{9}$/', Rule::unique('users', 'mobile_number')->ignore($user->id)],
            'notifications_enabled' => ['nullable', 'boolean'],
        ];

        if ($donor) {
            $rules += [
                'blood_type' => ['required', Rule::in(Donor::BLOOD_TYPES)],
                // Sanity checks only, as at registration. Eligibility rules belong to matching (M2.3, DEC-008).
                'birthdate' => ['required', 'date', 'before:today', 'after:1900-01-01'],
                'weight_kg' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:300'],
                'city' => ['required', 'string', 'max:100'],
                'location_sharing' => ['nullable', 'boolean'],
                // Approximate location from the map picker (M2.4). Both or neither.
                'approx_latitude' => ['nullable', 'numeric', 'required_with:approx_longitude',
                    'between:'.implode(',', config('lifeline.location_bounds.latitude'))],
                'approx_longitude' => ['nullable', 'numeric', 'required_with:approx_latitude',
                    'between:'.implode(',', config('lifeline.location_bounds.longitude'))],
            ];
        }

        $data = $request->validate($rules, self::MESSAGES);

        // Matching relies on blood type, so an admin reviews a changed one again.
        $needsReview = $donor
            && $donor->blood_type !== $data['blood_type']
            && $donor->verification_status !== 'pending';

        DB::transaction(function () use ($user, $donor, $data, $request, $needsReview) {
            $user->fill(Arr::only($data, ['first_name', 'last_name', 'mobile_number']));
            // Email preference (M2.5); a form without the field keeps the current choice.
            if ($request->exists('notifications_enabled')) {
                $user->notifications_enabled = $request->boolean('notifications_enabled');
            }
            $user->save();

            if (! $donor) {
                return;
            }

            $donor->fill(Arr::only($data, ['blood_type', 'birthdate', 'weight_kg', 'city']));
            $donor->location_sharing = $request->boolean('location_sharing');

            if ($donor->location_sharing && $request->exists('approx_latitude')) {
                // Rounded here, not by the database: 2 decimals is about 1 km, never the exact home (DEC-004).
                $donor->approx_latitude = isset($data['approx_latitude']) ? round((float) $data['approx_latitude'], 2) : null;
                $donor->approx_longitude = isset($data['approx_longitude']) ? round((float) $data['approx_longitude'], 2) : null;
            } elseif (! $donor->location_sharing) {
                // Do not keep a location the donor no longer shares (Data Privacy Act: data minimization).
                $donor->approx_latitude = null;
                $donor->approx_longitude = null;
            }

            if ($needsReview) {
                $donor->verification_status = 'pending';
                $donor->verified_by = null;
                $donor->verified_at = null;
            }

            $donor->save();
        });

        return redirect()->route('profile.show')->with('status', $needsReview
            ? 'Your profile was updated. Because your blood type changed, an administrator will verify your donor account again.'
            : 'Your profile was updated.');
    }
}
