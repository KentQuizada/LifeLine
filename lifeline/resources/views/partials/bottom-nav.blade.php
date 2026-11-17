{{-- A tab appears only once its page exists, so there are no placeholder screens.
     Route names are the contract for M2: map, requests.create, alerts.index, profile.show. --}}
@php
    $tabs = [
        ['route' => 'home', 'label' => 'Home', 'icon' => 'bi-house-door'],
        ['route' => 'map', 'label' => 'Map', 'icon' => 'bi-geo-alt'],
        ['route' => 'requests.create', 'label' => 'SOS', 'icon' => 'bi-plus-lg', 'class' => 'll-sos'],
        ['route' => 'alerts.index', 'label' => 'Alerts', 'icon' => 'bi-bell'],
        ['route' => 'profile.show', 'label' => 'Profile', 'icon' => 'bi-person'],
    ];
@endphp
<nav class="ll-bottom-nav fixed-bottom bg-white border-top" aria-label="Main">
    <ul class="nav justify-content-around container ll-narrow py-1">
        @foreach ($tabs as $tab)
            @if (Route::has($tab['route']))
                @php($isActive = request()->routeIs($tab['route']))
                <li class="nav-item">
                    <a href="{{ route($tab['route']) }}" class="nav-link {{ $tab['class'] ?? '' }} {{ $isActive ? 'active' : '' }}"
                       @if ($isActive) aria-current="page" @endif>
                        <span class="position-relative">
                            <i class="bi {{ $tab['icon'] }}" aria-hidden="true"></i>
                            @if ($tab['route'] === 'alerts.index')
                                @php($unread = auth()->user()->notifications()->whereNull('read_at')->count())
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger ll-nav-badge"
                                      data-unread-badge data-poll="{{ route('api.notifications.count') }}"
                                      @if ($unread === 0) hidden @endif>{{ $unread }}</span>
                            @endif
                        </span>
                        {{ $tab['label'] }}@if ($tab['route'] === 'alerts.index')<span class="visually-hidden" data-unread-text>{{ $unread > 0 ? ", {$unread} unread" : '' }}</span>@endif
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
