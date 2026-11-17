<?php

// Served under /api/v1 (bootstrap/app.php). Public routes are stateless. Signed-in routes add the
// `web` middleware group: the normal login session cookie, plus the CSRF token on POST/PATCH/DELETE (DEC-015).

use App\Http\Controllers\Api\DonorMapController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RequestMatchesController;
use App\Http\Controllers\Api\TrackRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/requests/track/{reference}', TrackRequestController::class)
    ->middleware('throttle:30,1,track')
    ->name('api.requests.track');

Route::middleware(['web', 'auth', 'verified', 'role:donor,requester'])->group(function () {
    Route::get('/requests/{bloodRequest}/matches', RequestMatchesController::class)
        ->where('bloodRequest', '[0-9]{1,18}')
        ->middleware('throttle:60,1,matches')
        ->name('api.requests.matches');

    Route::get('/map', DonorMapController::class)
        ->middleware('throttle:60,1,map')
        ->name('api.map');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->middleware('throttle:60,1,alerts')
        ->name('api.notifications.index');
    // The first API write: the browser sends the CSRF token as X-CSRF-TOKEN (DEC-015).
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])
        ->middleware('throttle:30,1,alerts-read')
        ->name('api.notifications.read');
});
