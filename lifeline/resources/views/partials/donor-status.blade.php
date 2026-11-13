{{-- Donor verification status card. Needs $donor. Used on home and my profile. --}}
@php
    $statuses = [
        'pending' => ['bi-hourglass-split', 'text-bg-warning', 'Waiting for verification', 'An administrator will review your donor account. Until then you will not appear in donor searches.'],
        'verified' => ['bi-patch-check-fill', 'text-bg-success', 'Verified donor', 'You can now be matched with blood requests.'],
        'rejected' => ['bi-x-octagon', 'text-bg-danger', 'Not verified', 'An administrator could not verify your donor account, so you will not appear in donor searches.'],
    ];
    [$icon, $badge, $title, $text] = $statuses[$donor->verification_status];
@endphp
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <span class="badge {{ $badge }} mb-2"><i class="bi {{ $icon }}" aria-hidden="true"></i> {{ $title }}</span>
        <p class="text-secondary small mb-0">{{ $text }}</p>
    </div>
</div>
