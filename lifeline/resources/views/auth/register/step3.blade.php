@extends('layouts.guest')

@section('title', 'Secure your account')

@section('content')
    @include('auth.register._steps', ['step' => 3])

    <h1 class="h4 fw-bold">Secure your account</h1>
    <p class="text-secondary">Choose a password and review how we use your information.</p>

    <form method="POST" action="{{ route('register.step3') }}" class="card card-body shadow-sm" data-submit-once>
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password"
                   aria-describedby="password_help"
                   class="form-control @error('password') is-invalid @enderror">
            <div id="password_help" class="form-text">At least 8 characters, with letters and numbers.</div>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required
                   autocomplete="new-password" class="form-control">
        </div>

        @if ($data['role'] === 'donor')
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="location_sharing" id="location_sharing" value="1"
                       @checked(old('location_sharing'))>
                <label class="form-check-label" for="location_sharing">
                    Share my approximate location (about 1 km) so I can be matched with requests near me. You can turn this off anytime.
                </label>
            </div>
        @endif

        <div class="form-check mb-2">
            <input class="form-check-input @error('privacy_consent') is-invalid @enderror" type="checkbox"
                   name="privacy_consent" id="privacy_consent" value="1" required @checked(old('privacy_consent'))>
            <label class="form-check-label" for="privacy_consent">
                I agree that LifeLine may store and process my personal and medical information for blood donor
                matching, in line with the Data Privacy Act of 2012 (RA 10173).
            </label>
            @error('privacy_consent')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
                   name="terms" id="terms" value="1" required @checked(old('terms'))>
            <label class="form-check-label" for="terms">I accept the LifeLine terms of use below.</label>
            @error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <details class="small text-secondary mb-3">
            <summary>Terms of use</summary>
            <ul class="mt-2 mb-0">
                <li>LifeLine only suggests potential donors. Final compatibility and eligibility are confirmed by healthcare professionals.</li>
                <li>Give truthful information and keep it up to date.</li>
                <li>Use other users' contact details only to arrange a blood donation.</li>
                <li>Administrators may verify, suspend, or remove accounts that break these terms.</li>
            </ul>
        </details>

        <button type="submit" class="btn btn-primary w-100">Create account</button>
    </form>

    <p class="text-center mt-3">
        <a href="{{ route($data['role'] === 'donor' ? 'register.step2' : 'register.step1') }}">Back</a>
    </p>
@endsection
