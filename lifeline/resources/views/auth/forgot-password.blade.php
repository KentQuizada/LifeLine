@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <h1 class="h4 fw-bold">Reset your password</h1>
    <p class="text-secondary">Enter your email and we will send you a link to choose a new password.</p>

    <form method="POST" action="{{ route('password.email') }}" class="card card-body shadow-sm">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary w-100">Send reset link</button>
    </form>

    <p class="text-center mt-3"><a href="{{ route('login') }}">Back to log in</a></p>
@endsection
