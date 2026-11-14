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
                        <i class="bi {{ $tab['icon'] }}" aria-hidden="true"></i>
                        {{ $tab['label'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
