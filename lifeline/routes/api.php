<?php

// Served under /api/v1 (bootstrap/app.php). Stateless for now (no session, so no logged-in user); authenticated API routes need an explicit decision (M2.3).

use App\Http\Controllers\Api\TrackRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/requests/track/{reference}', TrackRequestController::class)
    ->middleware('throttle:30,1,track')
    ->name('api.requests.track');
