@extends('layouts.guest')

@section('title', 'Choose a new password')

@section('content')
    <h1 class="h4 fw-bold">Choose a new password</h1>

    <form method="POST" action="{{ route('password.update') }}" class="card card-body shadow-sm">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password"
                   aria-describedby="password_help"
                   class="form-control @error('password') is-invalid @enderror">
            <div id="password_help" class="form-text">At least 8 characters, with letters and numbers.</div>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required
                   autocomplete="new-password" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary w-100">Reset password</button>
    </form>
@endsection
