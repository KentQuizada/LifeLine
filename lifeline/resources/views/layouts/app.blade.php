<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body class="ll-app {{ auth()->user()->role === 'admin' ? '' : 'll-has-bottom-nav' }}">
    <header class="navbar ll-topbar">
        <div class="container @yield('container_class', 'll-narrow')">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="bi bi-droplet-fill" aria-hidden="true"></i> LifeLine
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">Log out</button>
            </form>
        </div>
    </header>

    @if (auth()->user()->role === 'admin')
        @include('partials.admin-nav')
    @endif

    <main class="container @yield('container_class', 'll-narrow') py-3">
        @include('partials.alerts')
        @yield('content')
    </main>

    @if (auth()->user()->role !== 'admin')
        @include('partials.bottom-nav')
    @endif

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/lifeline.js') }}"></script>
    @stack('scripts')
</body>
</html>
