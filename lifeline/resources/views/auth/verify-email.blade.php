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

        <form method="POST" action="{{ route('verification.send') }}" class="mb-2">
            @csrf
            <button type="submit" class="btn btn-primary w-100">Resend verification email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link">Log out</button>
        </form>
    </div>
@endsection
