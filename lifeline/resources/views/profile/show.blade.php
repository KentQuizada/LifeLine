@extends('layouts.app')

@section('title', 'My profile')

@section('content')
    <section class="ll-hero text-center">
        <div class="ll-avatar mx-auto mb-2" aria-hidden="true">
            {{ mb_strtoupper(mb_substr($user->first_name, 0, 1).mb_substr($user->last_name, 0, 1)) }}
        </div>
        <h1 class="h4 fw-bold mb-0">{{ $user->first_name }} {{ $user->last_name }}</h1>
        <p class="ll-hero-muted mb-0">{{ ucfirst($user->role) }}@if ($donor) · {{ $donor->city }}@endif</p>
        @if ($donor)
            <span class="badge bg-white text-brand fs-6 mt-2" title="Blood type">{{ $donor->blood_type }}</span>

            <div class="row g-2 mt-3">
                <div class="col-6">
                    <div class="ll-hero-stat">
                        <div class="h4 fw-bold mb-0">{{ $donationCount }}</div>
                        <div class="small">Completed donations</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="ll-hero-stat">
                        <div class="fw-bold mb-0">{{ $lastDonation?->format('M j, Y') ?? 'None yet' }}</div>
                        <div class="small">Last donation</div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    @if ($donor)
        @include('partials.availability-card')
        @include('partials.donor-status')
    @endif

    <h2 class="ll-section-label">Account</h2>
    <div class="list-group shadow-sm mb-3">
        <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
            <span class="ll-menu-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span>
            <span class="flex-grow-1">
                <span class="d-block fw-semibold">Edit profile</span>
                <span class="small text-secondary">Name and mobile number</span>
            </span>
            <i class="bi bi-chevron-right text-secondary" aria-hidden="true"></i>
        </a>
        @if ($donor)
            <a href="{{ route('profile.edit') }}#medical" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                <span class="ll-menu-icon"><i class="bi bi-droplet" aria-hidden="true"></i></span>
                <span class="flex-grow-1">
                    <span class="d-block fw-semibold">Medical info</span>
                    <span class="small text-secondary">Blood type, birthdate, weight, city</span>
                </span>
                <i class="bi bi-chevron-right text-secondary" aria-hidden="true"></i>
            </a>
            <a href="{{ route('donations.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                <span class="ll-menu-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
                <span class="flex-grow-1">
                    <span class="d-block fw-semibold">Donation history</span>
                    <span class="small text-secondary">{{ $donationCount }} recorded</span>
                </span>
                <i class="bi bi-chevron-right text-secondary" aria-hidden="true"></i>
            </a>
        @endif
    </div>

    <h2 class="ll-section-label">Details</h2>
    <div class="card shadow-sm mb-3">
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between gap-3">
                <span class="text-secondary">Email</span><span class="text-end text-break">{{ $user->email }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between gap-3">
                <span class="text-secondary">Mobile number</span><span>{{ $user->mobile_number }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between gap-3">
                <span class="text-secondary">Email notifications</span><span>{{ $user->notifications_enabled ? 'On' : 'Off' }}</span>
            </li>
            @if ($donor)
                <li class="list-group-item d-flex justify-content-between gap-3">
                    <span class="text-secondary">Blood type</span><span>{{ $donor->blood_type }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between gap-3">
                    <span class="text-secondary">Age</span><span>{{ $donor->birthdate->age }} years</span>
                </li>
                <li class="list-group-item d-flex justify-content-between gap-3">
                    <span class="text-secondary">Weight</span><span>{{ (float) $donor->weight_kg }} kg</span>
                </li>
                <li class="list-group-item d-flex justify-content-between gap-3">
                    <span class="text-secondary">City</span><span>{{ $donor->city }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between gap-3">
                    <span class="text-secondary">Location sharing</span>
                    <span>{{ $donor->location_sharing ? 'On (about 1 km)' : 'Off' }}</span>
                </li>
            @endif
        </ul>
    </div>

    @if ($donor)
        <p class="small text-secondary">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            LifeLine only suggests potential donors. Final compatibility and eligibility are confirmed by healthcare professionals.
        </p>
    @endif

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-primary w-100 mb-2">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out
        </button>
    </form>
@endsection
