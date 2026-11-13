@extends('layouts.app')

@section('title', 'My profile')

@section('content')
    <div class="text-center mb-3">
        <div class="ll-avatar mx-auto mb-2" aria-hidden="true">
            {{ mb_strtoupper(mb_substr($user->first_name, 0, 1).mb_substr($user->last_name, 0, 1)) }}
        </div>
        <h1 class="h4 fw-bold mb-0">{{ $user->first_name }} {{ $user->last_name }}</h1>
        <p class="text-secondary mb-0">{{ ucfirst($user->role) }}</p>
        @if ($donor)
            <span class="badge text-bg-danger fs-6 mt-2" title="Blood type">{{ $donor->blood_type }}</span>
        @endif
    </div>

    @if ($donor)
        @include('partials.donor-status')

        <div class="row g-2 mb-3 text-center">
            <div class="col-6">
                <div class="card shadow-sm h-100"><div class="card-body">
                    <div class="h4 fw-bold text-brand mb-0">{{ $donationCount }}</div>
                    <div class="small text-secondary">Completed donations</div>
                </div></div>
            </div>
            <div class="col-6">
                <div class="card shadow-sm h-100"><div class="card-body">
                    <div class="fw-bold mb-0">{{ $lastDonation?->format('M j, Y') ?? 'None yet' }}</div>
                    <div class="small text-secondary">Last donation</div>
                </div></div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm mb-3">
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between gap-3">
                <span class="text-secondary">Email</span><span class="text-end text-break">{{ $user->email }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between gap-3">
                <span class="text-secondary">Mobile number</span><span>{{ $user->mobile_number }}</span>
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
@endsection
