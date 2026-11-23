<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use App\Models\User;
use App\Support\EmailTypo;
use App\Support\MobileNumber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * 3-step registration (design B1-B3). Each step is a normal form; answers are kept
 * in the session under "registration" until step 3 creates the account.
 * Requesters skip step 2 because medical info is for donors only.
 */
class RegisterController extends Controller
{
    // Never 'admin': administrators are created by the seeder (and later by another admin).
    private const ROLES = ['donor', 'requester'];

    private const STEP2_FIELDS = ['blood_type', 'birthdate', 'weight_kg', 'city'];

    private const MESSAGES = [
        'mobile_number.regex' => 'Enter a Philippine mobile number, for example 0917 123 4567.',
    ];

    public function showStep1(Request $request): View
    {
        return view('auth.register.step1', ['data' => $request->session()->get('registration', [])]);
    }

    public function storeStep1(Request $request): RedirectResponse
    {
        // Only strings are normalized; anything else (e.g. email[]=x) is left for validation to reject.
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower(trim($request->input('email')))]);
        }
        if (is_string($request->input('mobile_number'))) {
            $request->merge(['mobile_number' => MobileNumber::normalize($request->input('mobile_number')) ?? $request->input('mobile_number')]);
        }

        $data = $request->validate($this->step1Rules(), self::MESSAGES);

        // Changing the role starts the later steps over; resubmitting with the same role keeps the
        // medical answers already given in step 2.
        $previous = $request->session()->get('registration', []);
        if (($previous['role'] ?? null) === $data['role']) {
            $data += Arr::only($previous, self::STEP2_FIELDS);
        }
        $request->session()->put('registration', $data);

        return redirect()->route($data['role'] === 'donor' ? 'register.step2' : 'register.step3');
    }

    public function showStep2(Request $request): View|RedirectResponse
    {
        $registration = $request->session()->get('registration', []);

        if (($registration['role'] ?? null) !== 'donor') {
            return redirect()->route('register.step1');
        }

        return view('auth.register.step2', ['data' => $registration, 'bloodTypes' => Donor::BLOOD_TYPES]);
    }

    public function storeStep2(Request $request): RedirectResponse
    {
        $registration = $request->session()->get('registration', []);

        if (($registration['role'] ?? null) !== 'donor') {
            return redirect()->route('register.step1');
        }

        $data = $request->validate([
            'blood_type' => ['required', Rule::in(Donor::BLOOD_TYPES)],
            // Sanity checks only. Eligibility rules (age, weight) need a cited source (DEC-008) and belong to matching (M2.3).
            'birthdate' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'weight_kg' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:300'],
            'city' => ['required', 'string', 'max:100'],
        ]);

        $request->session()->put('registration', array_merge($registration, $data));

        return redirect()->route('register.step3');
    }

    public function showStep3(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfIncomplete($request)) {
            return $redirect;
        }

        return view('auth.register.step3', ['data' => $request->session()->get('registration')]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->redirectIfIncomplete($request)) {
            return $redirect;
        }

        $registration = $request->session()->get('registration');

        $account = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
            'privacy_consent' => ['accepted'],
            'terms' => ['accepted'],
            'location_sharing' => ['nullable', 'boolean'],
        ]);

        // Someone may have taken the email or mobile number since step 1.
        $recheck = Validator::make($registration, $this->step1Rules(), self::MESSAGES);
        if ($recheck->fails()) {
            return redirect()->route('register.step1')->withErrors($recheck);
        }

        try {
            $user = $this->createAccount($registration, $account, $request);
        } catch (UniqueConstraintViolationException) {
            // Another sign-up saved the same email or mobile number between the re-check and the insert.
            return redirect()->route('register.step1')
                ->withErrors(['email' => 'This email address or mobile number is already registered.']);
        }

        $request->session()->forget('registration');
        Auth::login($user);
        $request->session()->regenerate();

        if (config('lifeline.auto_verify_email')) {
            $user->markEmailAsVerified(); // DEC-024: no email service online

            return redirect()->route('home');
        }

        event(new Registered($user)); // sends the verification email

        return redirect()->route('verification.notice');
    }

    private function createAccount(array $registration, array $account, Request $request): User
    {
        return DB::transaction(function () use ($registration, $account, $request) {
            $user = new User([
                'first_name' => $registration['first_name'],
                'last_name' => $registration['last_name'],
                'email' => $registration['email'],
                'mobile_number' => $registration['mobile_number'],
                'password' => $account['password'],
            ]);
            $user->role = $registration['role']; // set explicitly: role is not fillable
            // Step 3 validated both checkboxes as `accepted`; keep the time as evidence.
            $user->privacy_consent_at = now();
            $user->terms_accepted_at = now();
            $user->save();

            if ($user->role === 'donor') {
                // verification_status defaults to 'pending' until an admin reviews the donor.
                $user->donor()->create([
                    'blood_type' => $registration['blood_type'],
                    'birthdate' => $registration['birthdate'],
                    'weight_kg' => $registration['weight_kg'],
                    'city' => $registration['city'],
                    'location_sharing' => $request->boolean('location_sharing'),
                ]);
            }

            return $user;
        });
    }

    private function redirectIfIncomplete(Request $request): ?RedirectResponse
    {
        $registration = $request->session()->get('registration', []);

        if (! isset($registration['role'])) {
            return redirect()->route('register.step1')->with('error', 'Please start your registration here.');
        }

        if ($registration['role'] === 'donor' && ! isset($registration['blood_type'])) {
            return redirect()->route('register.step2');
        }

        return null;
    }

    private function step1Rules(): array
    {
        return [
            'role' => ['required', Rule::in(self::ROLES)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', EmailTypo::rule(), 'unique:users,email'],
            'mobile_number' => ['required', 'regex:/^09\d{9}$/', 'unique:users,mobile_number'],
        ];
    }
}
