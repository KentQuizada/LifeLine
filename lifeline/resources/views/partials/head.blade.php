<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#d32f2f">
{{-- Plain-JS files send this as X-CSRF-TOKEN on /api/v1 writes (DEC-015). --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'LifeLine') | LifeLine</title>
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/lifeline.css') }}">
@stack('head')
