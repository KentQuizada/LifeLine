<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#d32f2f">
{{-- Installable PWA (DEC-001): "Add to Home screen" opens LifeLine like an app. --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
{{-- Plain-JS files send this as X-CSRF-TOKEN on /api/v1 writes (DEC-015). --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'LifeLine') | LifeLine</title>
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/lifeline.css') }}">
@stack('head')
