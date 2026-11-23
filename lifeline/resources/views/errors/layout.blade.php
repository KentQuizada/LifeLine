{{-- Error pages (FrontendUI "error pages"). Standalone on purpose: no session, CSRF or database needed,
     so the page still renders when the error came from one of those. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#d32f2f">
    <title>@yield('title') | LifeLine</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/lifeline.css') }}">
</head>
<body>
    <main class="container ll-narrow py-5 text-center">
        <p class="h4 fw-bold text-brand mb-4"><i class="bi bi-droplet-fill" aria-hidden="true"></i> LifeLine</p>
        <i class="bi @yield('icon', 'bi-exclamation-circle') display-3 text-secondary" aria-hidden="true"></i>
        <h1 class="h4 fw-bold mt-3">@yield('title')</h1>
        <p class="text-secondary">@yield('message')</p>
        <div class="d-grid gap-2 mt-4">
            @yield('actions')
            <a href="{{ url('/home') }}" class="btn btn-primary">Go to Home</a>
            <a href="{{ url('/track') }}" class="btn btn-outline-secondary">Track a request</a>
        </div>
        <p class="small text-secondary mt-4">Error {{ $exception->getStatusCode() }}</p>
    </main>
</body>
</html>
