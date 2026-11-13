@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <h1 class="h4 fw-bold">Hello, {{ $user->first_name }}!</h1>
    <p class="text-secondary">You are logged in as a {{ $user->role }}.</p>
@endsection
