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

        @if (! $donor->location_sharing)
            <div class="alert alert-warning small" role="status">
                Location sharing is off, so you do not appear in donor searches.
                <a href="{{ route('profile.edit') }}">Change this in your profile.</a>
            </div>
        @elseif ($donor->approx_latitude === null)
            <div class="alert alert-warning small" role="status">
                Set your approximate location so requesters near you can find you.
                <a href="{{ route('profile.edit') }}#location">Set location</a>
            </div>
        @endif

        <div class="d-grid gap-2">
            <a href="{{ route('matches.index') }}" class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-2">
                <i class="bi bi-heart-pulse" aria-hidden="true"></i> Requests for you
                @if ($waitingMatches > 0)
                    <span class="badge text-bg-danger">{{ $waitingMatches }} waiting</span>
                @endif
            </a>
            <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-check" aria-hidden="true"></i> My requests
            </a>
            <a href="{{ route('donations.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history" aria-hidden="true"></i> Donation history
            </a>
            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                <i class="bi bi-person" aria-hidden="true"></i> My profile
            </a>
        </div>
    @else
        <div class="d-grid gap-2">
            <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list-check" aria-hidden="true"></i> My requests
            </a>
            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                <i class="bi bi-person" aria-hidden="true"></i> My profile
            </a>
        </div>
    @endif
@endsection
