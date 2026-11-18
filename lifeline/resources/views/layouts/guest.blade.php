<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body class="ll-guest">
    <div class="ll-guest-wrap">
        {{-- Red brand panel: shown beside the form on laptops, hidden on phones (A1 splash design). --}}
        <aside class="ll-brand-panel d-none d-lg-flex" aria-hidden="true">
            <div class="ll-brand-inner">
                <div class="ll-brand-logo"><i class="bi bi-droplet-fill"></i></div>
                <p class="ll-brand-name">LifeLine</p>
                <p class="ll-brand-tagline">Find compatible blood donors near you: fast, verified and safe.</p>
                <ul class="ll-brand-points">
                    <li><i class="bi bi-broadcast"></i> Send an emergency request in minutes</li>
                    <li><i class="bi bi-geo-alt"></i> See available donors on a live map</li>
                    <li><i class="bi bi-shield-check"></i> Every donor is verified by an admin</li>
                </ul>
                <p class="ll-brand-foot">Butuan City · Caraga State University</p>
            </div>
        </aside>

        <main class="ll-guest-main">
            <div class="container ll-narrow py-4">
                @include('partials.alerts')
                @yield('content')
            </div>
        </main>
    </div>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/lifeline.js') }}"></script>
    @stack('scripts')
</body>
</html>
