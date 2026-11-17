<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The signed-in user's in-app alerts (M2.5, design C3), used by public/js/alerts.js.
 * Login session (DEC-015); the mark-read write needs the CSRF token, sent as X-CSRF-TOKEN.
 */
class NotificationController extends Controller
{
    private const LIMIT = 50;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = now()->startOfDay();

        $alerts = $user->notifications()->orderByDesc('id')->limit(self::LIMIT)->get();

        return response()->json([
            'unread_count' => $user->notifications()->whereNull('read_at')->count(),
            'data' => $alerts->map(fn (Notification $alert) => [
                'id' => $alert->id,
                'title' => $alert->title,
                'message' => $alert->message,
                'read' => $alert->read_at !== null,
                'group' => $alert->created_at->gte($today) ? 'today' : 'earlier',
                'time' => $alert->created_at->diffForHumans(),
                'url' => route('alerts.open', $alert->id),
            ])->all(),
        ]);
    }

    /** Mark all as read. */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['unread_count' => 0]);
    }
}
