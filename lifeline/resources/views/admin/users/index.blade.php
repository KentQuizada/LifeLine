@extends('layouts.app')

@section('title', 'Users')
@section('container_class', '')

@section('content')
    <h1 class="h4 fw-bold mb-3">Users</h1>

    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end mb-3" role="search">
        <div class="col-8 col-md-5">
            <label for="q" class="form-label small mb-1">Name, email or mobile</label>
            <input id="q" name="q" type="search" maxlength="100" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-4 col-md-3">
            <label for="role" class="form-label small mb-1">Role</label>
            <select id="role" name="role" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach (['donor', 'requester', 'admin'] as $role)
                    <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-grid">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </div>
    </form>

    @if ($users->isEmpty())
        <p class="text-secondary">No users found.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Role</th>
                        <th scope="col">Email</th>
                        <th scope="col">Mobile</th>
                        <th scope="col">Email verified</th>
                        <th scope="col">Requests</th>
                        <th scope="col">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                @if ($user->donor)
                                    <a href="{{ route('admin.donors.show', $user->donor) }}">{{ $user->first_name }} {{ $user->last_name }}</a>
                                @else
                                    {{ $user->first_name }} {{ $user->last_name }}
                                @endif
                            </td>
                            <td>
                                {{ ucfirst($user->role) }}
                                @if ($user->donor) @include('admin.donors.status-badge', ['status' => $user->donor->verification_status]) @endif
                            </td>
                            <td class="text-break">{{ $user->email }}</td>
                            <td>{{ $user->mobile_number }}</td>
                            <td>{{ $user->hasVerifiedEmail() ? 'Yes' : 'No' }}</td>
                            <td>{{ $user->blood_requests_count }}</td>
                            <td class="text-nowrap">{{ $user->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    @endif
@endsection
