<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use App\Models\Hospital;
use App\Support\Distance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Donor map data (M2.4, design C2), used by public/js/map.js. Only signed-in users (DEC-015).
 * Donors: searchable, with approximate coordinates (2 decimals, about 1 km), blood type and a whole-km
 * distance only. No ids, names or contact details: those are shared only after a donor accepts (DEC-004).
 */
class DonorMapController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'blood_type' => ['nullable', Rule::in(Donor::BLOOD_TYPES)],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ]);

        $usesDefault = ! isset($data['latitude']);
        $center = $usesDefault ? config('lifeline.map.center') : [
            'latitude' => round((float) $data['latitude'], 2),
            'longitude' => round((float) $data['longitude'], 2),
        ];

        $distance = fn ($latitude, $longitude): int => max(1, (int) round(Distance::km(
            (float) $center['latitude'], (float) $center['longitude'], (float) $latitude, (float) $longitude
        )));

        // Starts from searchable() like matching; the map also needs coordinates (Donors invariant).
        $donors = Donor::searchable()
            ->whereNotNull('approx_latitude')
            ->whereNotNull('approx_longitude')
            ->where('user_id', '!=', $request->user()->id)
            ->when($data['blood_type'] ?? null, fn ($query, $type) => $query->where('blood_type', $type))
            ->get(['blood_type', 'approx_latitude', 'approx_longitude'])
            ->map(fn (Donor $donor) => [
                'blood_type' => $donor->blood_type,
                'latitude' => round((float) $donor->approx_latitude, 2),
                'longitude' => round((float) $donor->approx_longitude, 2),
                'distance_km' => $distance($donor->approx_latitude, $donor->approx_longitude),
            ])
            ->sortBy('distance_km')
            ->take(config('lifeline.map.donor_limit'))
            ->values();

        $hospitals = Hospital::get(['name', 'type', 'latitude', 'longitude'])
            ->map(fn (Hospital $hospital) => [
                'name' => $hospital->name,
                'type' => $hospital->type,
                'latitude' => (float) $hospital->latitude,
                'longitude' => (float) $hospital->longitude,
                'distance_km' => $distance($hospital->latitude, $hospital->longitude),
            ])
            ->sortBy([['distance_km', 'asc'], ['name', 'asc']])
            ->values();

        return response()->json(['data' => [
            'center' => $center + ['source' => $usesDefault ? 'default' : 'user'],
            'donors' => $donors->all(),
            'hospitals' => $hospitals->all(),
        ]]);
    }
}
