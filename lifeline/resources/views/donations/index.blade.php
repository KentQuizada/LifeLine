@extends('layouts.app')

@section('title', 'Donation history')

@section('content')
    <h1 class="h4 fw-bold">Donation history</h1>
    <p class="text-secondary">Keep a record of when you donated blood.</p>

    <form method="POST" action="{{ route('donations.store') }}" class="card card-body shadow-sm mb-3">
        @csrf
        <h2 class="h6 fw-bold">Add a past donation</h2>

        <div class="mb-3">
            <label for="donated_on" class="form-label">Date donated</label>
            <input id="donated_on" name="donated_on" type="date" required
                   min="{{ $birthdate->copy()->addDay()->toDateString() }}" max="{{ now()->toDateString() }}"
                   value="{{ old('donated_on') }}"
                   class="form-control @error('donated_on') is-invalid @enderror">
            @error('donated_on')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="hospital_id" class="form-label">Hospital or blood bank (optional)</label>
            <select id="hospital_id" name="hospital_id" class="form-select @error('hospital_id') is-invalid @enderror">
                <option value="">Not listed / not sure</option>
                @foreach ($hospitals as $hospital)
                    <option value="{{ $hospital->id }}" @selected((string) old('hospital_id') === (string) $hospital->id)>{{ $hospital->name }}</option>
                @endforeach
            </select>
            @error('hospital_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Add donation</button>
    </form>

    @if ($donations->isEmpty())
        <p class="text-center text-secondary">No donations recorded yet.</p>
    @else
        <ul class="list-group shadow-sm mb-3">
            @foreach ($donations as $donation)
                <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-semibold">{{ $donation->donated_on->format('M j, Y') }}</div>
                        <div class="small text-secondary">{{ $donation->hospital?->name ?? 'Hospital not recorded' }}</div>
                        <span class="badge {{ $donation->is_self_reported ? 'text-bg-light border' : 'text-bg-success' }}">
                            {{ $donation->is_self_reported ? 'Self-reported' : 'Through LifeLine' }}
                        </span>
                    </div>
                    @if ($donation->is_self_reported)
                        <form method="POST" action="{{ route('donations.destroy', $donation) }}"
                              onsubmit="return confirm('Remove this donation from your history?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-secondary"
                                    aria-label="Remove donation on {{ $donation->donated_on->format('M j, Y') }}">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <p class="small text-secondary">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        LifeLine only suggests potential donors. Final compatibility and eligibility are confirmed by healthcare professionals.
    </p>
@endsection
