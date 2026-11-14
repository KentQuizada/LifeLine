@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <section class="ll-hero">
        <p class="ll-hero-muted small mb-0">Welcome back</p>
        <h1 class="h4 fw-bold mb-3">Hello, {{ $user->first_name }}!</h1>

        @if ($donor)
            <div class="row g-2">
                <div class="col-4">
                    <div class="ll-hero-stat">
                        <div class="h4 fw-bold mb-0">{{ $donor->blood_type }}</div>
                        <div class="small">Blood type</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="ll-hero-stat">
                        <div class="h4 fw-bold mb-0">{{ $donationCount }}</div>
                        <div class="small">Completed donations</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="ll-hero-stat">
                        <div class="fw-bold mb-0">{{ $lastDonation?->format('M j, Y') ?? 'None yet' }}</div>
                        <div class="small">Last donation</div>
                    </div>
                </div>
            </div>
        @else
            <p class="ll-hero-muted mb-0">LifeLine helps you find potential blood donors in Butuan City during emergencies.</p>
        @endif
    </section>

    <a href="{{ route('requests.create') }}" class="btn btn-primary btn-lg w-100 mb-3">
        <i class="bi bi-plus-circle-fill" aria-hidden="true"></i> Emergency request
    </a>

    @if ($donor)
        @include('partials.availability-card')

        @include('partials.donor-status')

        @unless ($donor->location_sharing)
            <div class="alert alert-warning small" role="status">
                Location sharing is off, so you do not appear in donor searches.
                <a href="{{ route('profile.edit') }}">Change this in your profile.</a>
            </div>
        @endunless

        <div class="d-grid gap-2">
            <a href="{{ route('donations.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history" aria-hidden="true"></i> Donation history
            </a>
            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                <i class="bi bi-person" aria-hidden="true"></i> My profile
            </a>
        </div>
    @else
        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-person" aria-hidden="true"></i> My profile
        </a>
    @endif
@endsection
