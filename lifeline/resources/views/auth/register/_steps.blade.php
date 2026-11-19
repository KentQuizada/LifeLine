{{-- Requesters have 2 steps (no medical info); donors have 3. Design B1-B3: "STEP 1 OF 3 ... BASIC INFO". --}}
@php
    $isRequester = ($data['role'] ?? null) === 'requester';
    $total = $isRequester ? 2 : 3;
    $number = $isRequester && $step === 3 ? 2 : $step;
    $name = [1 => 'Basic info', 2 => 'Medical info', 3 => 'Security'][$step];
@endphp
<div class="ll-steps mb-1">
    <span class="ll-step-now">Step {{ $number }} of {{ $total }}</span>
    <span class="ll-step-name">{{ $name }}</span>
</div>
<div class="ll-steps-bar mb-4" role="progressbar" aria-label="Registration progress"
     aria-valuenow="{{ $number }}" aria-valuemin="0" aria-valuemax="{{ $total }}">
    <span style="width: {{ round($number / $total * 100) }}%"></span>
</div>
