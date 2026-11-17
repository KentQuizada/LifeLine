@extends('layouts.app')

@section('title', 'Reports')
@section('container_class', '')

@section('content')
    <h1 class="h4 fw-bold mb-3">Reports</h1>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-end mb-3">
        <div class="col-5 col-md-3">
            <label for="from" class="form-label small mb-1">From</label>
            <input id="from" name="from" type="date" value="{{ $from->toDateString() }}" class="form-control form-control-sm @error('from') is-invalid @enderror">
            @error('from')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-5 col-md-3">
            <label for="to" class="form-label small mb-1">To</label>
            <input id="to" name="to" type="date" value="{{ $to->toDateString() }}" class="form-control form-control-sm @error('to') is-invalid @enderror">
            @error('to')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-2 col-md-2 d-grid">
            <button type="submit" class="btn btn-sm btn-primary">Show</button>
        </div>
    </form>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reports.requests', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}">
            <i class="bi bi-download" aria-hidden="true"></i> Requests in this period (CSV)
        </a>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reports.donors') }}">
            <i class="bi bi-download" aria-hidden="true"></i> All donors, without contact details (CSV)
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer" aria-hidden="true"></i> Print
        </button>
    </div>

    <p class="mb-3">
        <strong>{{ $total }}</strong> blood request(s) from {{ $from->format('M j, Y') }} to {{ $to->format('M j, Y') }}.
    </p>

    <div class="row g-3">
        <div class="col-md-4">
            <h2 class="h6 fw-bold">By status</h2>
            <table class="table table-sm bg-white">
                <tbody>
                    @foreach (\App\Models\BloodRequest::STATUS_LABELS as $value => $label)
                        <tr><th scope="row" class="fw-normal">{{ $label }}</th><td class="text-end">{{ $byStatus[$value] ?? 0 }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <h2 class="h6 fw-bold">By blood type</h2>
            <table class="table table-sm bg-white">
                <tbody>
                    @foreach ($bloodTypes as $type)
                        <tr><th scope="row" class="fw-normal">{{ $type }}</th><td class="text-end">{{ $byBloodType[$type] ?? 0 }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <h2 class="h6 fw-bold">By hospital</h2>
            @if ($byHospital->isEmpty())
                <p class="small text-secondary">No requests in this period.</p>
            @else
                <table class="table table-sm bg-white">
                    <tbody>
                        @foreach ($byHospital as $name => $count)
                            <tr><th scope="row" class="fw-normal">{{ $name }}</th><td class="text-end">{{ $count }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
