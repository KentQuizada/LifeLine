@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <div class="text-center mb-4">
        <span class="ll-auth-logo d-lg-none mb-3" aria-hidden="true"><i class="bi bi-droplet-fill"></i></span>
        <h1 class="h2 fw-bold mb-1">Welcome back</h1>
        <p class="text-secondary mb-0">Log in to your LifeLine account with your email or mobile number.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="card card-body shadow-sm" data-submit-once>
        @csrf

        <div class="mb-3">
            <label for="login" class="form-label">Email or mobile number</label>
            <div class="ll-field">
                <i class="bi bi-person" aria-hidden="true"></i>
                <input id="login" name="login" type="text" value="{{ old('login') }}" maxlength="255"
                       class="form-control @error('login') is-invalid @enderror"
                       placeholder="juan@email.com or 09XX XXX XXXX"
                       required autofocus autocomplete="username">
                @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-2">
            <label for="password" class="form-label">Password</label>
            <div class="ll-field">
                <i class="bi bi-lock" aria-hidden="true"></i>
                <input id="password" name="password" type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Your password"
                       required autocomplete="current-password">
                <button type="button" class="ll-eye" data-toggle-password="password" aria-label="Show password">
                    <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <p class="text-end small mb-3"><a href="{{ route('password.request') }}">Forgot password?</a></p>

        <button type="submit" class="btn btn-primary w-100">Log in <i class="bi bi-arrow-right" aria-hidden="true"></i></button>

        <p class="ll-divider my-3">or</p>

        <a href="{{ route('track') }}" class="btn btn-outline-primary w-100">
            <i class="bi bi-search" aria-hidden="true"></i> Track a blood request
        </a>
    </form>

    <p class="text-center mt-4">New to LifeLine? <a href="{{ route('register.step1') }}" class="fw-semibold">Create an account</a></p>
@endsection
