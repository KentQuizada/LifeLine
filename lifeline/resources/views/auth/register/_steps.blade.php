{{-- Requesters have 2 steps (no medical info); donors have 3. --}}
@php
    $isRequester = ($data['role'] ?? null) === 'requester';
    $total = $isRequester ? 2 : 3;
    $number = $isRequester && $step === 3 ? 2 : $step;
@endphp
<p class="text-secondary small mb-1">Step {{ $number }} of {{ $total }}</p>
<div class="progress mb-3" style="height: 6px" role="progressbar" aria-label="Registration progress"
     aria-valuenow="{{ $number }}" aria-valuemin="0" aria-valuemax="{{ $total }}">
    <div class="progress-bar bg-brand" style="width: {{ round($number / $total * 100) }}%"></div>
</div>
