@extends('layouts.guest')

@section('title', 'Create account')

@section('content')
    @include('auth.register._steps', ['step' => 1])

    <h1 class="h4 fw-bold">Create your account</h1>
    <p class="text-secondary">Tell us who you are.</p>

    <form method="POST" action="{{ route('register.step1') }}" class="card card-body shadow-sm">
        @csrf

        <fieldset class="mb-3">
            <legend class="form-label fs-6">I want to</legend>
            @foreach (['donor' => 'Donate blood', 'requester' => 'Request blood'] as $value => $label)
                <div class="form-check">
                    <input class="form-check-input @error('role') is-invalid @enderror" type="radio" name="role"
                           id="role_{{ $value }}" value="{{ $value }}" required
                           @checked(old('role', $data['role'] ?? '') === $value)>
                    <label class="form-check-label" for="role_{{ $value }}">{{ $label }}</label>
                </div>
            @endforeach
            @error('role')<div class="text-danger small">{{ $message }}</div>@enderror
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
