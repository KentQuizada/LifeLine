<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Design C3 Alerts tab. The list comes from GET /api/v1/notifications (Api\NotificationController). */
class AlertController extends Controller
{
    public function index(): View
    {
        return view('alerts.index');
    }

    /** Opening an alert marks it read and goes to the page it is about. */
    public function open(Request $request, int $notification): RedirectResponse
    {
        $user = $request->user();
        // Looked up through the user's own alerts, so other people's ids give 404.
        $alert = $user->notifications()->with('bloodRequest')->findOrFail($notification);

        if ($alert->read_at === null) {
            $alert->update(['read_at' => now()]);
        }

        $bloodRequest = $alert->bloodRequest;

        if ($bloodRequest && $bloodRequest->requester_id === $user->id) {
            return redirect()->route('requests.show', $bloodRequest->id);
        }

        if ($bloodRequest && $user->donor) {
            return redirect()->route('matches.index');
        }

        return redirect()->route('alerts.index');
    }
}
