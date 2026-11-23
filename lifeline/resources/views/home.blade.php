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

    <a href="{{ route('requests.create') }}" class="ll-sos-card mb-3">
        <span class="ll-sos-icon"><i class="bi bi-broadcast" aria-hidden="true"></i></span>
        <span class="flex-grow-1">
            <span class="d-block h5 fw-bold mb-0">Emergency request</span>
            <span class="d-block small">Find compatible blood donors fast</span>
        </span>
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
    </a>

    @if ($donor)
        @include('partials.availability-card')

        @include('partials.donor-status')

        {{-- DEC-026: donors without a location are still matched, after the donors near the hospital. --}}
        @if (! $donor->location_sharing)
            <div class="alert alert-info small" role="status">
                Location sharing is off: you still get requests, but after donors near the hospital.
                <a href="{{ route('profile.edit') }}#location">Share your location</a>
            </div>
        @elseif ($donor->approx_latitude === null)
            <div class="alert alert-info small" role="status">
                Set your approximate location so requests near you reach you first.
                <a href="{{ route('profile.edit') }}#location">Set location</a>
            </div>
        @endif

    @endif

    {{-- Shortcuts as menu rows (design E1 style). --}}
    @php
        $links = array_filter([
            $donor ? ['matches.index', 'bi-heart-pulse', 'Requests for you', 'Emergencies that match your blood type'] : null,
            ['requests.index', 'bi-list-check', 'My requests', 'Track the requests you sent'],
            $donor ? ['donations.index', 'bi-clock-history', 'Donation history', 'Your recorded donations'] : null,
            ['profile.show', 'bi-person', 'My profile', 'Account and settings'],
        ]);
    @endphp
    <h2 class="ll-section-label">Quick links</h2>
    <div class="card shadow-sm list-group list-group-flush mb-3">
        @foreach ($links as [$route, $icon, $label, $hint])
            <a href="{{ route($route) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                <span class="ll-menu-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                <span class="flex-grow-1">
                    <span class="d-block fw-semibold">{{ $label }}</span>
                    <span class="small text-secondary">{{ $hint }}</span>
                </span>
                @if ($route === 'matches.index' && $waitingMatches > 0)
                    <span class="badge text-bg-danger">{{ $waitingMatches }} waiting</span>
                @endif
                <i class="bi bi-chevron-right text-secondary" aria-hidden="true"></i>
            </a>
        @endforeach
    </div>
@endsection
