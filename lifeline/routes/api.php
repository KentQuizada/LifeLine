<?php

// Served under /api/v1 (bootstrap/app.php). Uses the normal login session when a route needs a user (DEC-001).

use App\Http\Controllers\Api\TrackRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/requests/track/{reference}', TrackRequestController::class)
    ->middleware('throttle:30,1,track')
    ->name('api.requests.track');
