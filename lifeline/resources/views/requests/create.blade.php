@extends('layouts.app')

@section('title', 'Emergency request')

@section('content')
    <h1 class="h4 fw-bold"><i class="bi bi-plus-circle-fill text-brand" aria-hidden="true"></i> Emergency blood request</h1>
    <p class="text-secondary">Tell us what the hospital needs. You will get a reference number to track the request.</p>

    <form method="POST" action="{{ route('requests.store') }}" class="card card-body shadow-sm mb-3">
        @csrf

        <fieldset class="mb-3">
            <legend class="form-label fs-6">Blood type needed</legend>
            <div class="row g-2">
                @foreach ($bloodTypes as $type)
                    <div class="col-3">
                        <input type="radio" class="btn-check" name="blood_type" id="blood_type_{{ $loop->index }}"
                               value="{{ $type }}" required @checked(old('blood_type') === $type)>
                        <label class="btn btn-outline-primary w-100 fw-bold" for="blood_type_{{ $loop->index }}">{{ $type }}</label>
                    </div>
                @endforeach
            </div>
            @error('blood_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </fieldset>

        <div class="mb-3">
            <label for="units" class="form-label">Units needed</label>
            <input id="units" name="units" type="number" min="1" max="10" step="1" required inputmode="numeric"
                   value="{{ old('units', 1) }}" aria-describedby="units_help"
                   class="form-control @error('units') is-invalid @enderror">
            <div id="units_help" class="form-text">1 to 10 units. Ask the hospital if you are not sure.</div>
            @error('units')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <fieldset class="mb-3">
            <legend class="form-label fs-6">Urgency</legend>
            <div class="row g-2">
                <div class="col-6">
                    <input type="radio" class="btn-check" name="urgency" id="urgency_critical" value="critical"
                           required @checked(old('urgency') === 'critical')>
                    <label class="btn btn-outline-primary w-100 fw-bold" for="urgency_critical">Critical</label>
                </div>
                <div class="col-6">
                    <input type="radio" class="btn-check" name="urgency" id="urgency_urgent" value="urgent"
                           required @checked(old('urgency') === 'urgent')>
                    <label class="btn btn-outline-primary w-100 fw-bold" for="urgency_urgent">Urgent</label>
                </div>
            </div>
            @error('urgency')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </fieldset>

        <div class="mb-3">
            <label for="hospital_id" class="form-label">Hospital or blood bank</label>
            <select id="hospital_id" name="hospital_id" required aria-describedby="hospital_help"
                    class="form-select @error('hospital_id') is-invalid @enderror">
                <option value="">Choose where the blood is needed</option>
                @foreach ($hospitals as $hospital)
                    <option value="{{ $hospital->id }}" @selected((string) old('hospital_id') === (string) $hospital->id)>{{ $hospital->name }}</option>
                @endforeach
            </select>
            <div id="hospital_help" class="form-text">Potential donors are found near this location.</div>
            @error('hospital_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="patient_name" class="form-label">Patient name</label>
            <input id="patient_name" name="patient_name" type="text" maxlength="150" required autocomplete="off"
                   value="{{ old('patient_name') }}" aria-describedby="patient_help"
                   class="form-control @error('patient_name') is-invalid @enderror">
            <div id="patient_help" class="form-text">Never shown on the public tracking page.</div>
            @error('patient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes (optional)</label>
            <textarea id="notes" name="notes" rows="2" maxlength="500" aria-describedby="notes_help"
                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
            <div id="notes_help" class="form-text">For example, the ward or when the blood is needed.</div>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">
            <i class="bi bi-send-fill" aria-hidden="true"></i> Send emergency request
        </button>
    </form>

    <p class="small text-secondary">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        LifeLine only suggests potential donors. Final compatibility and eligibility are confirmed by healthcare professionals.
    </p>
@endsection
