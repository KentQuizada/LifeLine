<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body>
    <main class="container ll-narrow py-4">
        @include('partials.alerts')
        @yield('content')
    </main>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/lifeline.js') }}"></script>
    @stack('scripts')
</body>
</html>
