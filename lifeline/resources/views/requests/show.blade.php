@extends('layouts.app')

@section('title', 'Request '.$bloodRequest->reference_number)

@section('content')
    @if (session('submitted'))
        {{-- Design D2: shown once, right after the request is sent. --}}
        <section class="ll-hero text-center">
            <i class="bi bi-check-circle-fill display-5" aria-hidden="true"></i>
            <h1 class="h4 fw-bold mt-2">Request sent</h1>
            <p class="ll-hero-muted mb-2">Keep this reference number to track your request.</p>
            <p class="ll-reference mb-0">{{ $bloodRequest->reference_number }}</p>
        </section>
    @else
        <h1 class="h4 fw-bold">Blood request</h1>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <div class="small text-secondary">Reference number</div>
                    <div class="fw-bold font-monospace">{{ $bloodRequest->reference_number }}</div>
                </div>
                @include('partials.request-status', ['status' => $bloodRequest->status])
            </div>

            <dl class="row small mb-0">
                <dt class="col-5">Blood type</dt>
                <dd class="col-7">{{ $bloodRequest->blood_type }}</dd>
                <dt class="col-5">Units</dt>
                <dd class="col-7">{{ $bloodRequest->units }}</dd>
                <dt class="col-5">Urgency</dt>
                <dd class="col-7">{{ ucfirst($bloodRequest->urgency) }}</dd>
                <dt class="col-5">Hospital</dt>
                <dd class="col-7">{{ $bloodRequest->hospital->name }}</dd>
                <dt class="col-5">Patient</dt>
                <dd class="col-7">{{ $bloodRequest->patient_name }}</dd>
                @if ($bloodRequest->notes)
                    <dt class="col-5">Notes</dt>
                    <dd class="col-7">{{ $bloodRequest->notes }}</dd>
                @endif
                <dt class="col-5">Submitted</dt>
                <dd class="col-7 mb-0">{{ $bloodRequest->created_at->format('M j, Y g:i A') }}</dd>
            </dl>
        </div>
    </div>

    <h2 class="ll-section-label">Status history</h2>
    <ol class="list-group list-group-numbered shadow-sm mb-3">
        @foreach ($bloodRequest->history as $entry)
            <li class="list-group-item">
                <span class="fw-semibold">{{ \App\Models\BloodRequest::STATUS_LABELS[$entry->new_status] }}</span>
                <div class="small text-secondary">
                    {{ $entry->created_at->format('M j, Y g:i A') }}@if ($entry->remarks) · {{ $entry->remarks }}@endif
                </div>
            </li>
        @endforeach
    </ol>

    @if ($bloodRequest->canChangeTo('fulfilled') || $bloodRequest->canChangeTo('cancelled'))
        <div class="d-grid gap-2 mb-3">
            @if ($bloodRequest->canChangeTo('fulfilled'))
                <form method="POST" action="{{ route('requests.status', $bloodRequest->id) }}"
                      onsubmit="return confirm('Mark this request as fulfilled? This cannot be undone.')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="fulfilled">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check2-circle" aria-hidden="true"></i> Mark fulfilled
                    </button>
                </form>
            @endif
            @if ($bloodRequest->canChangeTo('cancelled'))
                <form method="POST" action="{{ route('requests.status', $bloodRequest->id) }}"
                      onsubmit="return confirm('Cancel this request? This cannot be undone.')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-x-circle" aria-hidden="true"></i> Cancel request
                    </button>
                </form>
            @endif
        </div>
    @endif

    <p class="text-center"><a href="{{ route('requests.index') }}">All my requests</a></p>

    <p class="small text-secondary">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        LifeLine only suggests potential donors. Final compatibility and eligibility are confirmed by healthcare professionals.
    </p>
@endsection
