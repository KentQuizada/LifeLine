@extends('layouts.guest')

@section('title', 'Welcome')

@section('content')
    {{-- Design A1-A4: logo, then three slides with a big round icon, a small label and a large title. --}}
    @php
        $slides = [
            ['icon' => 'bi-broadcast', 'green' => false, 'kicker' => 'Emergency ready', 'title' => 'Find blood donors in minutes',
             'text' => 'No more hours of posts and calls. Send one emergency request and LifeLine alerts compatible donors near the hospital.'],
            ['icon' => 'bi-people-fill', 'green' => false, 'kicker' => 'Smart matching', 'title' => 'The right donors, nearest first',
             'text' => 'LifeLine checks blood type compatibility, availability and the '.config('lifeline.donation_interval_months').'-month donation interval, then lists the nearest verified donors.'],
            ['icon' => 'bi-heart-fill', 'green' => true, 'kicker' => 'Save lives', 'title' => 'Be a hero in your community',
             'text' => 'Register as a donor and get alerted when someone needs your blood type. Your exact home is never shown, and your number only after you accept.'],
        ];
    @endphp

    <div class="text-center pt-2 pb-3 d-lg-none">
        <span class="ll-auth-logo mb-2" aria-hidden="true"><i class="bi bi-droplet-fill"></i></span>
        <h1 class="fw-bold mb-0">Life<span class="text-brand">Line</span></h1>
        <p class="text-secondary mb-0">Blood donor finder for Butuan City</p>
    </div>
    <h1 class="visually-hidden d-none d-lg-block">LifeLine</h1>

    <div id="onboarding" class="carousel slide mb-4" aria-label="About LifeLine">
        <div class="carousel-inner">
            @foreach ($slides as $slide)
                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                    <div class="card card-body text-center py-4 px-4">
                        <div class="mb-3">
                            <span class="ll-slide-icon {{ $slide['green'] ? 'is-green' : '' }}" aria-hidden="true">
                                <i class="bi {{ $slide['icon'] }}"></i>
                            </span>
                        </div>
                        <div><span class="ll-kicker {{ $slide['green'] ? 'is-green' : '' }}">{{ $slide['kicker'] }}</span></div>
                        <h2 class="ll-slide-title mt-2 mb-2">{{ $slide['title'] }}</h2>
                        <p class="text-secondary mb-0">{{ $slide['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="carousel-indicators position-static mt-3 mb-0">
            @foreach ($slides as $slide)
                <button type="button" data-bs-target="#onboarding" data-bs-slide-to="{{ $loop->index }}"
                        class="{{ $loop->first ? 'active' : '' }}"
                        @if ($loop->first) aria-current="true" @endif
                        aria-label="Slide {{ $loop->iteration }}: {{ $slide['title'] }}"></button>
            @endforeach
        </div>
        <div class="d-flex justify-content-between mt-2">
            <button class="btn btn-link text-secondary text-decoration-none" type="button" data-bs-target="#onboarding" data-bs-slide="prev">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back
            </button>
            <button class="btn btn-link text-decoration-none fw-semibold" type="button" data-bs-target="#onboarding" data-bs-slide="next">
                Next <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <a href="{{ route('register.step1') }}" class="btn btn-primary btn-lg w-100 mb-2">Get started <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">I already have an account</a>
@endsection
