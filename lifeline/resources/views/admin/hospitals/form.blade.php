@extends('layouts.app')

@section('title', $hospital->exists ? 'Edit hospital' : 'Add hospital')
@section('container_class', '')

@section('content')
    <p class="mb-2"><a href="{{ route('admin.hospitals.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Hospitals</a></p>
    <h1 class="h4 fw-bold mb-3">{{ $hospital->exists ? 'Edit '.$hospital->name : 'Add a hospital or blood bank' }}</h1>

    <form method="POST" action="{{ $hospital->exists ? route('admin.hospitals.update', $hospital) : route('admin.hospitals.store') }}"
          class="card card-body shadow-sm ll-narrow">
        @csrf
        @if ($hospital->exists) @method('PUT') @endif

        @php
            $fields = [
                ['name', 'Name', 'text', 150],
                ['address', 'Address', 'text', 255],
                ['city', 'City', 'text', 100],
                ['contact_number', 'Contact number (optional)', 'tel', 20],
            ];
        @endphp

        @foreach ($fields as [$field, $label, $type, $max])
            <div class="mb-3">
                <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" maxlength="{{ $max }}" @if ($field !== 'contact_number') required @endif
                       value="{{ old($field, $hospital->$field) }}" class="form-control @error($field) is-invalid @enderror">
                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @endforeach

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select id="type" name="type" required class="form-select @error('type') is-invalid @enderror">
                <option value="hospital" @selected(old('type', $hospital->type) === 'hospital')>Hospital</option>
                <option value="blood_bank" @selected(old('type', $hospital->type) === 'blood_bank')>Blood bank</option>
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label for="latitude" class="form-label">Latitude</label>
                <input id="latitude" name="latitude" type="number" step="0.000001" required inputmode="decimal"
                       value="{{ old('latitude', $hospital->latitude) }}" class="form-control @error('latitude') is-invalid @enderror">
                @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-6">
                <label for="longitude" class="form-label">Longitude</label>
                <input id="longitude" name="longitude" type="number" step="0.000001" required inputmode="decimal"
                       value="{{ old('longitude', $hospital->longitude) }}" class="form-control @error('longitude') is-invalid @enderror">
                @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 form-text">Used to find donors near the hospital, and as a location choice for donors. Copy the coordinates from OpenStreetMap or Google Maps.</div>
        </div>

        <button type="submit" class="btn btn-primary">{{ $hospital->exists ? 'Save changes' : 'Add hospital' }}</button>
    </form>
@endsection
