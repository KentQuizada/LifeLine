@extends('layouts.app')

@section('title', 'Requests for you')

@section('content')
    @php
        $badges = [
            'notified' => ['text-bg-warning', 'Waiting for your answer'],
            'accepted' => ['text-bg-success', 'You accepted'],
            'declined' => ['text-bg-secondary', 'You declined'],
        ];
    @endphp

    <h1 class="h4 fw-bold">Requests for you</h1>
    <p class="small text-secondary">Nearby patients who may receive your blood type. Your answer tells the requester who is coming.</p>

    @forelse ($matches as $match)
        @php($bloodRequest = $match->bloodRequest)
        @php($stillLooking = in_array($bloodRequest->status, \App\Models\BloodRequest::LOOKING_FOR_DONORS, true))
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <div class="fw-bold">{{ $bloodRequest->blood_type }} blood · {{ $bloodRequest->units }} unit(s)</div>
                        <div class="small text-secondary">
                            {{ ucfirst($bloodRequest->urgency) }} · about {{ max(1, (int) round((float) $match->distance_km)) }} km away
                        </div>
                    </div>
                    @php($badge = ($match->status === 'notified' && ! $stillLooking) ? ['text-bg-secondary', 'Closed'] : $badges[$match->status])
                    <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                </div>

                <dl class="row small mb-2">
                    <dt class="col-5">Hospital</dt>
                    <dd class="col-7">{{ $bloodRequest->hospital->name }}<br>{{ $bloodRequest->hospital->address }}</dd>
                    <dt class="col-5">Requested</dt>
                    <dd class="col-7">{{ $bloodRequest->created_at->format('M j, Y g:i A') }}</dd>
                    @if ($match->status === 'accepted')
                        {{-- Shared only after this donor accepts (DEC-004). --}}
                        <dt class="col-5">Patient</dt>
                        <dd class="col-7">{{ $bloodRequest->patient_name }}</dd>
                        <dt class="col-5">Requester</dt>
                        <dd class="col-7">
                            {{ $bloodRequest->requester->first_name }} {{ $bloodRequest->requester->last_name }}<br>
                            <a href="tel:{{ $bloodRequest->requester->mobile_number }}">{{ $bloodRequest->requester->mobile_number }}</a>
                            · <a href="sms:{{ $bloodRequest->requester->mobile_number }}">SMS</a>
                        </dd>
                        @if ($bloodRequest->hospital->contact_number)
                            <dt class="col-5">Hospital phone</dt>
                            <dd class="col-7 mb-0">
                                <a href="tel:{{ $bloodRequest->hospital->contact_number }}">{{ $bloodRequest->hospital->contact_number }}</a>
                            </dd>
                        @endif
                    @endif
                </dl>

                @if (! $stillLooking)
                    <p class="small text-secondary mb-0">
                        This request is closed ({{ \App\Models\BloodRequest::STATUS_LABELS[$bloodRequest->status] }}).
                    </p>
                @elseif ($match->status === 'notified')
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('matches.update', $match->id) }}"
                              onsubmit="return confirm('Accept? Your name and mobile number will be shared with the requester.')">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="answer" value="accepted">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-heart-pulse" aria-hidden="true"></i> I can donate
                            </button>
                        </form>
                        <form method="POST" action="{{ route('matches.update', $match->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="answer" value="declined">
                            <button type="submit" class="btn btn-outline-secondary w-100">Not this time</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-secondary">
            No requests for you yet. When a nearby patient may receive your blood type, the request appears here.
        </div>
    @endforelse

    {{ $matches->links() }}

    <p class="small text-secondary">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        LifeLine only suggests potential donors. Final compatibility and eligibility are confirmed by healthcare professionals.
    </p>
@endsection
