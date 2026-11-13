@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <h1 class="h4 fw-bold">Hello, {{ $user->first_name }}!</h1>

    @if ($donor)
        @include('partials.donor-status')
    @endif
@endsection
