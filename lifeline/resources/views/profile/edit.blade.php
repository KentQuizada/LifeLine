@extends('layouts.app')

@section('title', 'Edit profile')

@section('content')
    <h1 class="h4 fw-bold">Edit profile</h1>

    <form method="POST" action="{{ route('profile.update') }}" class="card card-body shadow-sm mb-3">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="first_name" class="form-label">First name</label>
            <input id="first_name" name="first_name" type="text" maxlength="100" required autocomplete="given-name"
                   value="{{ old('first_name', $user->first_name) }}"
                   class="form-control @error('first_name') is-invalid @enderror">
            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="last_name" class="form-label">Last name</label>
            <input id="last_name" name="last_name" type="text" maxlength="100" required autocomplete="family-name"
                   value="{{ old('last_name', $user->last_name) }}"
                   class="form-control @error('last_name') is-invalid @enderror">
            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" value="{{ $user->email }}" readonly disabled
                   aria-describedby="email_help" class="form-control">
            <div id="email_help" class="form-text">Your email address cannot be changed.</div>
        </div>

        <div class="mb-3">
            <label for="mobile_number" class="form-label">Mobile number</label>
            <input id="mobile_number" name="mobile_number" type="tel" inputmode="tel" required autocomplete="tel"
                   value="{{ old('mobile_number', $user->mobile_number) }}"
                   class="form-control @error('mobile_number') is-invalid @enderror">
            @error('mobile_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @if ($donor)
            <h2 class="h6 fw-bold mt-2">Medical information</h2>

            <div class="mb-3">
                <label for="blood_type" class="form-label">Blood type</label>
                <select id="blood_type" name="blood_type" required aria-describedby="blood_type_help"
                        class="form-select @error('blood_type') is-invalid @enderror">
                    @foreach ($bloodTypes as $type)
                        <option value="{{ $type }}" @selected(old('blood_type', $donor->blood_type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                <div id="blood_type_help" class="form-text">Changing your blood type means an administrator verifies your donor account again.</div>
                @error('blood_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="birthdate" class="form-label">Birthdate</label>
                <input id="birthdate" name="birthdate" type="date" required max="{{ now()->subDay()->toDateString() }}"
                       value="{{ old('birthdate', $donor->birthdate->toDateString()) }}"
                       class="form-control @error('birthdate') is-invalid @enderror">
                @error('birthdate')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="weight_kg" class="form-label">Weight (kg)</label>
                <input id="weight_kg" name="weight_kg" type="number" step="0.1" min="1" max="300" required inputmode="decimal"
                       value="{{ old('weight_kg', (float) $donor->weight_kg) }}"
                       class="form-control @error('weight_kg') is-invalid @enderror">
                @error('weight_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="city" class="form-label">City</label>
                <input id="city" name="city" type="text" maxlength="100" required autocomplete="address-level2"
                       value="{{ old('city', $donor->city) }}"
                       class="form-control @error('city') is-invalid @enderror">
                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" name="location_sharing" id="location_sharing" value="1"
                       @checked(old('location_sharing', $donor->location_sharing))>
                <label class="form-check-label" for="location_sharing">
                    Show my approximate location (about 1 km) on the donor map. When this is off, you do not appear in donor searches.
                </label>
            </div>
        @endif

        <button type="submit" class="btn btn-primary w-100">Save changes</button>
    </form>

    <p class="text-center"><a href="{{ route('profile.show') }}">Cancel</a></p>
@endsection
