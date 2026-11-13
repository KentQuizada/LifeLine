@extends('layouts.guest')

@section('title', 'Medical information')

@section('content')
    @include('auth.register._steps', ['step' => 2])

    <h1 class="h4 fw-bold">Medical information</h1>
    <p class="text-secondary">This helps requesters find a compatible donor.</p>

    <form method="POST" action="{{ route('register.step2') }}" class="card card-body shadow-sm">
        @csrf

        <div class="mb-3">
            <label for="blood_type" class="form-label">Blood type</label>
            <select id="blood_type" name="blood_type" required
                    class="form-select @error('blood_type') is-invalid @enderror">
                <option value="">Choose…</option>
                @foreach ($bloodTypes as $type)
                    <option value="{{ $type }}" @selected(old('blood_type', $data['blood_type'] ?? '') === $type)>{{ $type }}</option>
                @endforeach
            </select>
            @error('blood_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="birthdate" class="form-label">Birthdate</label>
            <input id="birthdate" name="birthdate" type="date" required max="{{ now()->subDay()->toDateString() }}"
                   value="{{ old('birthdate', $data['birthdate'] ?? '') }}"
                   class="form-control @error('birthdate') is-invalid @enderror">
            @error('birthdate')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="weight_kg" class="form-label">Weight (kg)</label>
            <input id="weight_kg" name="weight_kg" type="number" step="0.1" min="1" max="300" required inputmode="decimal"
                   value="{{ old('weight_kg', $data['weight_kg'] ?? '') }}"
                   class="form-control @error('weight_kg') is-invalid @enderror">
            @error('weight_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="city" class="form-label">City</label>
            <input id="city" name="city" type="text" maxlength="100" required autocomplete="address-level2"
                   value="{{ old('city', $data['city'] ?? 'Butuan City') }}"
                   class="form-control @error('city') is-invalid @enderror">
            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <p class="small text-secondary">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            LifeLine only suggests potential donors. Final compatibility and eligibility are confirmed by healthcare professionals.
        </p>

        <button type="submit" class="btn btn-primary w-100">Next</button>
    </form>

    <p class="text-center mt-3"><a href="{{ route('register.step1') }}">Back</a></p>
@endsection
