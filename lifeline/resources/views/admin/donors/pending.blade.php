@extends('layouts.app')

@section('title', 'Donor verification')
@section('container_class', '')

@section('content')
    <h1 class="h4 fw-bold mb-3">Donors waiting for verification</h1>

    @if ($donors->isEmpty())
        <p class="text-secondary">No donors are waiting for verification.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Blood type</th>
                        <th scope="col">City</th>
                        <th scope="col">Email verified</th>
                        <th scope="col">Registered</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($donors as $donor)
                        @php($name = $donor->user->first_name.' '.$donor->user->last_name)
                        <tr>
                            <td>
                                {{ $name }}<br>
                                <span class="small text-secondary">{{ $donor->user->email }} · {{ $donor->user->mobile_number }}</span>
                            </td>
                            <td>{{ $donor->blood_type }}</td>
                            <td>{{ $donor->city }}</td>
                            <td>{{ $donor->user->hasVerifiedEmail() ? 'Yes' : 'No' }}</td>
                            <td>{{ $donor->created_at->format('M j, Y') }}</td>
                            <td class="text-end text-nowrap">
                                <form method="POST" action="{{ route('admin.donors.verify', $donor) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" aria-label="Verify {{ $name }}">Verify</button>
                                </form>
                                <form method="POST" action="{{ route('admin.donors.reject', $donor) }}" class="d-inline"
                                      onsubmit="return confirm('Reject this donor?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Reject {{ $name }}">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $donors->links() }}
    @endif
@endsection
