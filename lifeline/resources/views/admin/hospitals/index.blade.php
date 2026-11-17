@extends('layouts.app')

@section('title', 'Hospitals')
@section('container_class', '')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0">Hospitals and blood banks</h1>
        <a href="{{ route('admin.hospitals.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add</a>
    </div>

    @if ($hospitals->isEmpty())
        <p class="text-secondary">No hospitals yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Type</th>
                        <th scope="col">Address</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Requests</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hospitals as $hospital)
                        <tr>
                            <td>{{ $hospital->name }}</td>
                            <td>{{ $hospital->type === 'blood_bank' ? 'Blood bank' : 'Hospital' }}</td>
                            <td>{{ $hospital->address }}, {{ $hospital->city }}</td>
                            <td>{{ $hospital->contact_number ?? '—' }}</td>
                            <td>{{ $hospital->blood_requests_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.hospitals.edit', $hospital) }}" class="btn btn-sm btn-outline-secondary"
                                   aria-label="Edit {{ $hospital->name }}">Edit</a>
                                <form method="POST" action="{{ route('admin.hospitals.destroy', $hospital) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this hospital?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete {{ $hospital->name }}"
                                            @disabled($hospital->blood_requests_count > 0)>Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $hospitals->links() }}
    @endif
@endsection
