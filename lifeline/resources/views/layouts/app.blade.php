<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body class="{{ auth()->user()->role === 'admin' ? '' : 'll-has-bottom-nav' }}">
    <header class="navbar bg-white border-bottom">
        <div class="container @yield('container_class', 'll-narrow')">
            <a class="navbar-brand fw-bold text-brand" href="{{ route('home') }}">
                <i class="bi bi-droplet-fill" aria-hidden="true"></i> LifeLine
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Log out</button>
            </form>
        </div>
    </header>

    <main class="container @yield('container_class', 'll-narrow') py-3">
        @include('partials.alerts')
        @yield('content')
    </main>

    @if (auth()->user()->role !== 'admin')
        @include('partials.bottom-nav')
    @endif

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
