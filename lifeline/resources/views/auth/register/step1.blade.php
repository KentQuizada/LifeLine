@extends('layouts.guest')

@section('title', 'Create account')

@section('content')
    @include('auth.register._steps', ['step' => 1])

    <h1 class="h4 fw-bold">Create your account</h1>
    <p class="text-secondary">Tell us who you are.</p>

    <form method="POST" action="{{ route('register.step1') }}" class="card card-body shadow-sm">
        @csrf

        <fieldset class="mb-3">
            <legend class="form-label fs-6">I want to register as</legend>
            <div class="row g-2">
                @foreach ([
                    'donor' => ['bi-droplet-fill', 'Blood donor', 'I can donate blood'],
                    'requester' => ['bi-hospital', 'Requester', 'I need blood for a patient'],
                ] as $value => [$icon, $label, $hint])
                    <div class="col-6">
                        <input class="btn-check" type="radio" name="role"
                               id="role_{{ $value }}" value="{{ $value }}" required
                               @checked(old('role', $data['role'] ?? '') === $value)>
                        <label class="btn btn-outline-primary w-100 fw-bold ll-role" for="role_{{ $value }}">
                            <i class="bi {{ $icon }}" aria-hidden="true"></i> {{ $label }}
                            <small>{{ $hint }}</small>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </fieldset>

        <div class="row g-2">
            <div class="col-sm-6 mb-3">
                <label for="first_name" class="form-label">First name</label>
                <input id="first_name" name="first_name" type="text" maxlength="100" required autocomplete="given-name"
                       value="{{ old('first_name', $data['first_name'] ?? '') }}"
                       class="form-control @error('first_name') is-invalid @enderror">
                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-6 mb-3">
                <label for="last_name" class="form-label">Last name</label>
                <input id="last_name" name="last_name" type="text" maxlength="100" required autocomplete="family-name"
                       value="{{ old('last_name', $data['last_name'] ?? '') }}"
                       class="form-control @error('last_name') is-invalid @enderror">
                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" maxlength="255" required autocomplete="email"
                   value="{{ old('email', $data['email'] ?? '') }}"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="mobile_number" class="form-label">Mobile number</label>
            <input id="mobile_number" name="mobile_number" type="tel" inputmode="tel" required autocomplete="tel"
                   placeholder="0917 123 4567" pattern="[0-9+()\s\-]{10,18}"
                   value="{{ old('mobile_number', $data['mobile_number'] ?? '') }}"
                   class="form-control @error('mobile_number') is-invalid @enderror">
            @error('mobile_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Next</button>
    </form>

    <p class="text-center mt-3">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
