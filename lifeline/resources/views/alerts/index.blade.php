@extends('layouts.app')

@section('title', 'Alerts')

@section('content')
    <div id="alerts-app" data-api="{{ route('api.notifications.index') }}" data-read-api="{{ route('api.notifications.read') }}">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="h4 fw-bold mb-0">Alerts</h1>
            <button type="button" class="btn btn-sm btn-outline-primary" data-mark-read disabled>Mark all as read</button>
        </div>
        <p class="small text-secondary mb-3" data-alerts-status role="status" aria-live="polite">Loading alerts…</p>
        <div data-alerts-list></div>
    </div>

    <p class="small text-secondary">
        <i class="bi bi-envelope" aria-hidden="true"></i>
        Email copies of these alerts can be turned on or off in <a href="{{ route('profile.edit') }}#notifications">your profile</a>.
    </p>
@endsection

@push('scripts')
    <script src="{{ asset('js/alerts.js') }}"></script>
@endpush
