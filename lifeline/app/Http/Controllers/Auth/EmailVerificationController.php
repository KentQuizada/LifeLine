<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\EmailTypo;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('home')
            : view('auth.verify-email');
    }

    // EmailVerificationRequest rejects (403) a link whose id or email hash is not the logged-in user's.
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('home')->with('status', 'Your email address is verified.');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    }

    /**
     * Someone who typed a wrong address at sign-up never gets the link and cannot register again
     * (the mobile number is taken), so a not-yet-verified user may correct the address here.
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }

        $email = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', EmailTypo::rule(),
                Rule::unique('users', 'email')->ignore($user->id)],
        ])['email'];

        $user->forceFill(['email' => $email, 'email_verified_at' => null])->save();
        $user->sendEmailVerificationNotification();

        return back()->with('status', "Email address changed. A new verification link has been sent to {$email}.");
    }
}
