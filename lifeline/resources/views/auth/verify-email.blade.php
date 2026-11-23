@extends('layouts.guest')

@section('title', 'Verify your email')

@section('content')
    <div class="card card-body shadow-sm text-center">
        <i class="bi bi-envelope-check text-brand display-4" aria-hidden="true"></i>
        <h1 class="h4 fw-bold mt-2">Check your email</h1>
        <p>
            We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
            Click it to activate your LifeLine account.
        </p>

        <p class="small text-secondary">
            Not in your inbox? Check the <strong>Spam</strong> or <strong>Promotions</strong> folder. The email comes
            from LifeLine through Brevo (brevosend.com).
        </p>

        <form method="POST" action="{{ route('verification.send') }}" class="mb-3" data-submit-once>
            @csrf
            <button type="submit" class="btn btn-primary w-100">Resend verification email</button>
        </form>

        <details class="text-start mb-2" @if ($errors->has('email')) open @endif>
            <summary class="small fw-semibold">Wrong email address? Change it</summary>
            <form method="POST" action="{{ route('verification.address') }}" class="mt-2" data-submit-once>
                @csrf
                @method('PUT')
                <label for="email" class="form-label small">Correct email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255"
                       autocomplete="email" class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <button type="submit" class="btn btn-outline-primary w-100 mt-2">Save and send the link there</button>
            </form>
        </details>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link">Log out</button>
        </form>
    </div>
@endsection
