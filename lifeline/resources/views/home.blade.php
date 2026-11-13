@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <h1 class="h4 fw-bold">Hello, {{ $user->first_name }}!</h1>

    @if ($donor)
        @include('partials.availability-card')

        @include('partials.donor-status')

        <div class="row g-2 mb-3 text-center">
            <div class="col-4">
                <div class="card shadow-sm h-100"><div class="card-body px-1">
                    <div class="h4 fw-bold text-brand mb-0">{{ $donor->blood_type }}</div>
                    <div class="small text-secondary">Blood type</div>
                </div></div>
            </div>
            <div class="col-4">
                <div class="card shadow-sm h-100"><div class="card-body px-1">
                    <div class="h4 fw-bold mb-0">{{ $donationCount }}</div>
                    <div class="small text-secondary">Completed donations</div>
                </div></div>
            </div>
            <div class="col-4">
                <div class="card shadow-sm h-100"><div class="card-body px-1">
                    <div class="fw-bold mb-0">{{ $lastDonation?->format('M j, Y') ?? 'None yet' }}</div>
                    <div class="small text-secondary">Last donation</div>
                </div></div>
            </div>
        </div>

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
        <p class="text-secondary">LifeLine helps you find potential blood donors in Butuan City during emergencies.</p>
        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-person" aria-hidden="true"></i> My profile
        </a>
    @endif
@endsection
