<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use Illuminate\View\View;

/** Design C2 donor map. The data comes from GET /api/v1/map (Api\DonorMapController). */
class MapController extends Controller
{
    public function __invoke(): View
    {
        return view('map.index', [
            'bloodTypes' => Donor::BLOOD_TYPES,
            'center' => config('lifeline.map.center'),
        ]);
    }
}
