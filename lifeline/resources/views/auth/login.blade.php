@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <div class="text-center mb-4">
        <i class="bi bi-droplet-fill text-brand display-4" aria-hidden="true"></i>
        <h1 class="h3 fw-bold mt-2">Welcome back</h1>
        <p class="text-secondary">Log in with your email or mobile number.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="card card-body shadow-sm">
        @csrf

        <div class="mb-3">
            <label for="login" class="form-label">Email or mobile number</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" maxlength="255"
                   class="form-control @error('login') is-invalid @enderror"
                   required autofocus autocomplete="username">
            @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   required autocomplete="current-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Log in</button>
    </form>

    <p class="text-center mt-3">New to LifeLine? <a href="{{ route('register.step1') }}">Create an account</a></p>
@endsection
