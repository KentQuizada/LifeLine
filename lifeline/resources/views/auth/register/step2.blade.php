@extends('layouts.guest')

@section('title', 'Medical information')

@section('content')
    @include('auth.register._steps', ['step' => 2])

    <h1 class="h4 fw-bold">Medical information</h1>
    <p class="text-secondary">This helps requesters find a compatible donor.</p>

    <form method="POST" action="{{ route('register.step2') }}" class="card card-body shadow-sm">
        @csrf

        {{-- Blood type tiles (design B2), the same as the SOS form. --}}
        <fieldset class="mb-3">
            <legend class="form-label fs-6">Blood type</legend>
            <div class="row g-2">
                @foreach ($bloodTypes as $type)
                    <div class="col-3">
                        <input type="radio" class="btn-check" name="blood_type" id="blood_type_{{ $loop->index }}"
                               value="{{ $type }}" required @checked(old('blood_type', $data['blood_type'] ?? '') === $type)>
                        <label class="btn btn-outline-primary w-100 fw-bold" for="blood_type_{{ $loop->index }}">{{ $type }}</label>
                    </div>
                @endforeach
            </div>
            @error('blood_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </fieldset>

        <div class="mb-3">
            <label for="birthdate" class="form-label">Birthdate</label>
            <input id="birthdate" name="birthdate" type="date" required max="{{ now()->subDay()->toDateString() }}"
                   value="{{ old('birthdate', $data['birthdate'] ?? '') }}"
                   class="form-control @error('birthdate') is-invalid @enderror">
            @error('birthdate')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="weight_kg" class="form-label">Weight (kg)</label>
            <input id="weight_kg" name="weight_kg" type="number" step="0.01" min="1" max="300" required inputmode="decimal"
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
