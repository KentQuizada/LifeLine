@extends('layouts.app')

@section('title', 'Donors')
@section('container_class', '')

@section('content')
    <h1 class="h4 fw-bold mb-3">Donors</h1>

    <form method="GET" action="{{ route('admin.donors.index') }}" class="row g-2 align-items-end mb-3" role="search">
        <div class="col-12 col-md-4">
            <label for="q" class="form-label small mb-1">Name, email or mobile</label>
            <input id="q" name="q" type="search" maxlength="100" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm">
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
            <label for="status" class="form-label small mb-1">Verification</label>
            <select id="status" name="status" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach (['pending', 'verified', 'rejected'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-md-2">
            <label for="available" class="form-label small mb-1">Availability</label>
            <select id="available" name="available" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="1" @selected(($filters['available'] ?? '') === '1')>Available</option>
                <option value="0" @selected(($filters['available'] ?? '') === '0')>Not available</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-grid">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </div>
    </form>

    @if ($donors->isEmpty())
        <p class="text-secondary">No donors found.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Blood type</th>
                        <th scope="col">City</th>
                        <th scope="col">Verification</th>
                        <th scope="col">Available</th>
                        <th scope="col">Location</th>
                        <th scope="col">Registered</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($donors as $donor)
                        <tr>
                            <td>
                                <a href="{{ route('admin.donors.show', $donor) }}">{{ $donor->user->first_name }} {{ $donor->user->last_name }}</a><br>
                                <span class="small text-secondary">{{ $donor->user->email }}</span>
                            </td>
                            <td>{{ $donor->blood_type }}</td>
                            <td>{{ $donor->city }}</td>
                            <td>@include('admin.donors.status-badge', ['status' => $donor->verification_status])</td>
                            <td>{{ $donor->is_available ? 'Yes' : 'No' }}</td>
                            <td>{{ $donor->location_sharing ? ($donor->approx_latitude !== null ? 'Shared' : 'Not set') : 'Off' }}</td>
                            <td class="text-nowrap">{{ $donor->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $donors->links() }}
    @endif
@endsection
