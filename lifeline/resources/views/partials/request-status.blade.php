{{-- Blood request status badge. Needs $status. --}}
@php
    $badges = [
        'open' => 'text-bg-warning',
        'donor_found' => 'text-bg-info',
        'fulfilled' => 'text-bg-success',
        'cancelled' => 'text-bg-secondary',
        'expired' => 'text-bg-secondary',
    ];
@endphp
<span class="badge {{ $badges[$status] }}">{{ \App\Models\BloodRequest::STATUS_LABELS[$status] }}</span>
