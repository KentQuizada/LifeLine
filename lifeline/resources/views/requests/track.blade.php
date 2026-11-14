@extends(auth()->check() ? 'layouts.app' : 'layouts.guest')

@section('title', 'Track a request')

@section('content')
    <h1 class="h4 fw-bold"><i class="bi bi-search text-brand" aria-hidden="true"></i> Track a request</h1>
    <p class="text-secondary">Enter the reference number you received, for example LL-261114-ABC234.</p>

    <form id="track-form" class="card card-body shadow-sm mb-3" data-api="{{ url('/api/v1/requests/track') }}">
        <label for="reference" class="form-label">Reference number</label>
        <input id="reference" name="reference" type="text" required maxlength="16"
               pattern="[Ll][Ll]-[0-9]{6}-[A-Za-z0-9]{6}" title="LL-YYMMDD-XXXXXX"
               autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="LL-YYMMDD-XXXXXX"
               class="form-control font-monospace text-uppercase mb-3">
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-search" aria-hidden="true"></i> Track
        </button>
    </form>

    <div id="track-result" aria-live="polite"></div>

    <noscript>
        <div class="alert alert-warning">Tracking needs JavaScript. Please turn it on in your browser.</div>
    </noscript>

    <p class="small text-secondary mt-3">
        <i class="bi bi-shield-lock" aria-hidden="true"></i>
        This page shows the request status only. Patient and contact details are never shown.
    </p>

    @guest
        <p class="text-center"><a href="{{ route('login') }}">Log in</a></p>
    @endguest

    <script src="{{ asset('js/track.js') }}" defer></script>
@endsection
