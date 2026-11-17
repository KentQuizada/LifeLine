<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Support\Statistics;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/** Admin dashboard (M2.6): number cards from Statistics, charts drawn by public/js/admin-dashboard.js. */
class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => Statistics::summary(),
            'recentRequests' => BloodRequest::with('hospital')->orderByDesc('id')->limit(5)->get(),
        ]);
    }

    /** GET /api/v1/admin/stats: the same numbers as JSON for the charts. */
    public function stats(): JsonResponse
    {
        return response()->json(['data' => Statistics::summary()]);
    }
}
