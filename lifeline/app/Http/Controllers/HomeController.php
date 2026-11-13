<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // One login form for every role; admins land in their own area.
        if ($user->role === 'admin') {
            return redirect()->route('admin.donors.pending');
        }

        return view('home', ['user' => $user, 'donor' => $user->donor]);
    }
}
