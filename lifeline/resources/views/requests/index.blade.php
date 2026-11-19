@extends('layouts.app')

@section('title', 'My requests')

@section('content')
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0">My requests</h1>
        <a href="{{ route('requests.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle" aria-hidden="true"></i> New request
        </a>
    </div>

    @if ($bloodRequests->isEmpty())
        <div class="card card-body shadow-sm text-center text-secondary">
            No requests yet. Use the SOS button when someone needs blood.
        </div>
    @else
        <div class="list-group shadow-sm mb-3">
            @foreach ($bloodRequests as $bloodRequest)
                <a href="{{ route('requests.show', $bloodRequest->id) }}" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                    <span class="ll-blood" aria-hidden="true">{{ $bloodRequest->blood_type }}</span>
                    <span class="flex-grow-1">
                        <span class="d-flex flex-wrap justify-content-between align-items-start gap-1">
                            <span class="fw-bold font-monospace text-nowrap">{{ $bloodRequest->reference_number }}</span>
                            @include('partials.request-status', ['status' => $bloodRequest->status])
                        </span>
                        <span class="d-block small text-secondary">
                            {{ $bloodRequest->blood_type }} · {{ $bloodRequest->units }} {{ Str::plural('unit', $bloodRequest->units) }} · {{ $bloodRequest->hospital->name }}
                        </span>
                        <span class="d-block small text-secondary">{{ $bloodRequest->created_at->format('M j, Y g:i A') }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
@endsection
