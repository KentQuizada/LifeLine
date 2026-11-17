@extends('layouts.app')

@section('title', 'Donor map')

@push('head')
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
@endpush

@section('content')
    <h1 class="h4 fw-bold">Donor map</h1>
    <p class="small text-secondary">
        Available, verified donors near you. Circles are approximate (about 1 km); exact homes are never shown.
    </p>

    <div id="donor-map-app" data-api="{{ route('api.map') }}"
         data-center-lat="{{ $center['latitude'] }}" data-center-lng="{{ $center['longitude'] }}">
        <div class="row g-2 align-items-end mb-2">
            <div class="col-7">
                <label for="map_blood_type" class="form-label small mb-1">Blood type</label>
                <select id="map_blood_type" class="form-select form-select-sm">
                    <option value="">All blood types</option>
                    @foreach ($bloodTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-5 d-grid">
                <button type="button" class="btn btn-sm btn-outline-primary" data-use-location>
                    <i class="bi bi-crosshair" aria-hidden="true"></i> Near me
                </button>
            </div>
        </div>

        <p class="small mb-2" data-map-status role="status" aria-live="polite">Loading donors…</p>

        <div class="ll-map rounded border mb-3" data-map aria-label="Map of approximate donor locations and hospitals"></div>

        <h2 class="ll-section-label">Nearby donors</h2>
        <ul class="list-group shadow-sm mb-3" data-donor-list></ul>

        <h2 class="ll-section-label">Hospitals and blood banks</h2>
        <ul class="list-group shadow-sm mb-3" data-hospital-list></ul>
    </div>

    <p class="small text-secondary">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        These are potential donors only. Contact details are shared after a donor accepts your request, and final
        compatibility and eligibility are confirmed by healthcare professionals.
    </p>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('js/map.js') }}"></script>
@endpush
