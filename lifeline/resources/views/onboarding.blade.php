@extends('layouts.guest')

@section('title', 'Welcome')

@section('content')
    @php
        $slides = [
            ['icon' => 'bi-search-heart', 'title' => 'Find donors fast', 'text' => 'Search verified volunteer donors by blood type and distance.'],
            ['icon' => 'bi-broadcast', 'title' => 'Send an SOS request', 'text' => 'Post an emergency blood request and alert compatible donors near the hospital.'],
            ['icon' => 'bi-shield-check', 'title' => 'Private and safe', 'text' => 'Donors appear only at an approximate location, and contact details are shared only after a donor accepts.'],
        ];
    @endphp

    <div class="text-center pt-4 pb-3">
        <i class="bi bi-droplet-fill text-brand" style="font-size: 4rem" aria-hidden="true"></i>
        <h1 class="fw-bold text-brand mb-0">LifeLine</h1>
        <p class="text-secondary">Blood donor finder for Butuan City</p>
    </div>

    <div id="onboarding" class="carousel carousel-dark slide mb-4" aria-label="About LifeLine">
        <div class="carousel-inner text-center">
            @foreach ($slides as $slide)
                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                    <div class="card card-body shadow-sm mx-5 py-4">
                        <i class="bi {{ $slide['icon'] }} text-brand display-5" aria-hidden="true"></i>
                        <h2 class="h5 fw-bold mt-2">{{ $slide['title'] }}</h2>
                        <p class="text-secondary mb-0">{{ $slide['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#onboarding" data-bs-slide="prev" style="width: 2.5rem">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#onboarding" data-bs-slide="next" style="width: 2.5rem">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
        <div class="carousel-indicators position-static mt-3 mb-0">
            @foreach ($slides as $slide)
                <button type="button" data-bs-target="#onboarding" data-bs-slide-to="{{ $loop->index }}"
                        class="{{ $loop->first ? 'active' : '' }}"
                        @if ($loop->first) aria-current="true" @endif
                        aria-label="Slide {{ $loop->iteration }}: {{ $slide['title'] }}"></button>
            @endforeach
        </div>
    </div>

    <a href="{{ route('register.step1') }}" class="btn btn-primary btn-lg w-100 mb-2">Get started</a>
    <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">I already have an account</a>
@endsection
