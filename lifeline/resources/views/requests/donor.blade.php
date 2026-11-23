@extends('layouts.app')

@section('title', 'Donor profile')

@section('content')
    {{-- Design D3: shown only for a donor who accepted this request (DEC-004, DEC-022). --}}
    @php($user = $donor->user)
    <section class="ll-hero">
        <div class="d-flex align-items-center gap-3">
            <span class="ll-avatar" aria-hidden="true">{{ mb_substr($user->first_name, 0, 1) }}{{ mb_substr($user->last_name, 0, 1) }}</span>
            <div>
                <h1 class="h4 fw-bold mb-0">{{ $user->first_name }} {{ $user->last_name }}</h1>
                <p class="ll-hero-muted mb-0">
                    <i class="bi bi-patch-check-fill" aria-hidden="true"></i> Verified donor · {{ $donor->city }}
                </p>
            </div>
        </div>
        <div class="row g-2 mt-3">
            <div class="col-4">
                <div class="ll-hero-stat">
                    <div class="fs-5 fw-bold">{{ $donor->blood_type }}</div>
                    <div class="small">Blood type</div>
                </div>
            </div>
            <div class="col-4">
                <div class="ll-hero-stat">
                    <div class="fs-5 fw-bold">{{ $donationCount }}</div>
                    <div class="small">Donations</div>
                </div>
            </div>
            <div class="col-4">
                <div class="ll-hero-stat">
                    <div class="fs-5 fw-bold">{{ $match->distance_km === null ? 'Unknown' : max(1, (int) round((float) $match->distance_km)).' km' }}</div>
                    <div class="small">From hospital</div>
                </div>
            </div>
        </div>
    </section>

    <h2 class="ll-section-label">Contact</h2>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <p class="mb-2 fw-semibold">{{ $user->mobile_number }}</p>
            <div class="d-flex gap-2">
                <a href="tel:{{ $user->mobile_number }}" class="btn btn-success flex-fill">
                    <i class="bi bi-telephone" aria-hidden="true"></i> Call
                </a>
                <a href="sms:{{ $user->mobile_number }}" class="btn btn-outline-success flex-fill">
                    <i class="bi bi-chat-dots" aria-hidden="true"></i> SMS
                </a>
            </div>
            <p class="small text-secondary mb-0 mt-2">
                Accepted request {{ $bloodRequest->reference_number }}
                @if ($match->responded_at) on {{ $match->responded_at->format('M j, Y g:i A') }}@endif.
                Last donation: {{ $lastDonation ? $lastDonation->format('M j, Y') : 'none recorded' }}.
            </p>
        </div>
    </div>

    <h2 class="ll-section-label">Donation</h2>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            @if ($donation)
                <p class="mb-0">
                    <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                    Donation recorded on {{ $donation->donated_on->format('M j, Y') }} at {{ $bloodRequest->hospital->name }}.
                </p>
            @elseif ($canRecord)
                <p class="small text-secondary">After the donor gives blood at the hospital, record it here. It goes into the donor's donation history.</p>
                <form method="POST" action="{{ route('requests.donor.donation', [$bloodRequest->id, $match->id]) }}" data-submit-once>
                    @csrf
                    <label for="donated_on" class="form-label">Donation date</label>
                    <input id="donated_on" name="donated_on" type="date" required
                           value="{{ old('donated_on', now()->toDateString()) }}"
                           min="{{ $bloodRequest->created_at->toDateString() }}" max="{{ now()->toDateString() }}"
                           class="form-control mb-3 @error('donated_on') is-invalid @enderror">
                    @error('donated_on')<div class="invalid-feedback d-block mt-n2 mb-3">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-droplet-fill" aria-hidden="true"></i> Record donation
                    </button>
                </form>
            @else
                <p class="small text-secondary mb-0">
                    This request is {{ strtolower(\App\Models\BloodRequest::STATUS_LABELS[$bloodRequest->status]) }}, so a donation can no longer be recorded.
                </p>
            @endif
        </div>
    </div>

    <p class="text-center">
        <a href="{{ route('requests.show', $bloodRequest->id) }}">Back to request {{ $bloodRequest->reference_number }}</a>
    </p>

    <p class="small text-secondary">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        LifeLine only suggests potential donors. Final compatibility and eligibility are confirmed by healthcare professionals.
    </p>
@endsection
