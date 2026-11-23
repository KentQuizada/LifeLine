@extends('layouts.app')

@section('title', 'Request '.$bloodRequest->reference_number)
@section('container_class', '')

@section('content')
    <p class="mb-2"><a href="{{ route('admin.requests.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> All requests</a></p>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0">Request <span class="font-monospace">{{ $bloodRequest->reference_number }}</span></h1>
        @include('partials.request-status', ['status' => $bloodRequest->status])
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Details</h2>
                    <dl class="row small mb-0">
                        <dt class="col-4">Patient</dt><dd class="col-8">{{ $bloodRequest->patient_name }}</dd>
                        <dt class="col-4">Blood type</dt><dd class="col-8">{{ $bloodRequest->blood_type }}</dd>
                        <dt class="col-4">Units</dt><dd class="col-8">{{ $bloodRequest->units }}</dd>
                        <dt class="col-4">Urgency</dt><dd class="col-8">{{ ucfirst($bloodRequest->urgency) }}</dd>
                        <dt class="col-4">Hospital</dt><dd class="col-8">{{ $bloodRequest->hospital->name }}</dd>
                        <dt class="col-4">Requester</dt>
                        <dd class="col-8">{{ $bloodRequest->requester->first_name }} {{ $bloodRequest->requester->last_name }} · {{ $bloodRequest->requester->mobile_number }}</dd>
                        @if ($bloodRequest->notes)
                            <dt class="col-4">Notes</dt><dd class="col-8">{{ $bloodRequest->notes }}</dd>
                        @endif
                        <dt class="col-4">Submitted</dt><dd class="col-8 mb-0">{{ $bloodRequest->created_at->format('M j, Y g:i A') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Change status</h2>
                    @if (empty($overrideStatuses))
                        <p class="small text-secondary mb-0">This request is closed. Its status can no longer change.</p>
                    @else
                        <form method="POST" action="{{ route('admin.requests.status', $bloodRequest) }}" class="row g-2">
                            @csrf
                            @method('PATCH')
                            <div class="col-md-4">
                                <label for="status" class="form-label small mb-1">New status</label>
                                <select id="status" name="status" required class="form-select form-select-sm @error('status') is-invalid @enderror">
                                    @foreach ($overrideStatuses as $status)
                                        <option value="{{ $status }}" @selected(old('status') === $status)>{{ \App\Models\BloodRequest::STATUS_LABELS[$status] }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label for="remarks" class="form-label small mb-1">Reason (saved in the status history)</label>
                                <input id="remarks" name="remarks" type="text" maxlength="200" required value="{{ old('remarks') }}"
                                       class="form-control form-control-sm @error('remarks') is-invalid @enderror">
                                @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-primary"
                                        onclick="return confirm('Change the status of this request? The requester is notified.')">Save status</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <h2 class="h6 fw-bold">Status history</h2>
            <ol class="list-group list-group-numbered shadow-sm mb-3">
                @foreach ($bloodRequest->history as $entry)
                    <li class="list-group-item">
                        <span class="fw-semibold">{{ \App\Models\BloodRequest::STATUS_LABELS[$entry->new_status] }}</span>
                        <div class="small text-secondary">
                            {{ $entry->created_at->format('M j, Y g:i A') }}
                            @if ($entry->changedBy) · by {{ $entry->changedBy->first_name }} {{ $entry->changedBy->last_name }} ({{ $entry->changedBy->role }})@endif
                            @if ($entry->remarks) · {{ $entry->remarks }}@endif
                        </div>
                    </li>
                @endforeach
            </ol>

            <h2 class="h6 fw-bold">Potential donors ({{ $bloodRequest->matches->count() }})</h2>
            @if ($bloodRequest->matches->isEmpty())
                <p class="small text-secondary">No potential donors matched yet.</p>
            @else
                <ul class="list-group shadow-sm mb-3">
                    @foreach ($bloodRequest->matches as $match)
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                            <a href="{{ route('admin.donors.show', $match->donor) }}">
                                {{ $match->donor->user->first_name }} {{ $match->donor->user->last_name }}
                            </a>
                            <span class="small text-secondary">{{ $match->donor->blood_type }} · {{ $match->distanceLabel() }}</span>
                            <span class="badge {{ $match->status === 'accepted' ? 'text-bg-success' : ($match->status === 'declined' ? 'text-bg-secondary' : 'text-bg-warning') }}">
                                {{ \App\Models\DonorMatch::STATUS_LABELS[$match->status] }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
