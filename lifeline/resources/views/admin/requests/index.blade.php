@extends('layouts.app')

@section('title', 'Blood requests')
@section('container_class', '')

@section('content')
    <h1 class="h4 fw-bold mb-3">Blood requests</h1>

    <form method="GET" action="{{ route('admin.requests.index') }}" class="row g-2 align-items-end mb-3" role="search">
        <div class="col-12 col-md-4">
            <label for="q" class="form-label small mb-1">Reference number or patient</label>
            <input id="q" name="q" type="search" maxlength="100" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-4 col-md-2">
            <label for="status" class="form-label small mb-1">Status</label>
            <select id="status" name="status" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach (\App\Models\BloodRequest::STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-md-2">
            <label for="blood_type" class="form-label small mb-1">Blood type</label>
            <select id="blood_type" name="blood_type" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach ($bloodTypes as $type)
                    <option value="{{ $type }}" @selected(($filters['blood_type'] ?? '') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-md-2">
            <label for="urgency" class="form-label small mb-1">Urgency</label>
            <select id="urgency" name="urgency" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="critical" @selected(($filters['urgency'] ?? '') === 'critical')>Critical</option>
                <option value="urgent" @selected(($filters['urgency'] ?? '') === 'urgent')>Urgent</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-grid">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </div>
    </form>

    @include('admin.requests.table')

    {{ $bloodRequests->links() }}
@endsection
