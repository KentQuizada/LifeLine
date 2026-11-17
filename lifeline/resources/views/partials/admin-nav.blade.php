{{-- M2.6 admin area navigation (desktop layout, not in the mobile design). --}}
@php
    $adminLinks = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
        ['route' => 'admin.donors.pending', 'match' => 'admin.donors.pending', 'label' => 'Verify donors', 'icon' => 'bi-patch-check'],
        ['route' => 'admin.donors.index', 'match' => 'admin.donors.index|admin.donors.show', 'label' => 'Donors', 'icon' => 'bi-droplet'],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'Users', 'icon' => 'bi-people'],
        ['route' => 'admin.requests.index', 'match' => 'admin.requests.*', 'label' => 'Requests', 'icon' => 'bi-clipboard2-pulse'],
        ['route' => 'admin.hospitals.index', 'match' => 'admin.hospitals.*', 'label' => 'Hospitals', 'icon' => 'bi-hospital'],
        ['route' => 'admin.reports.index', 'match' => 'admin.reports.*', 'label' => 'Reports', 'icon' => 'bi-file-earmark-bar-graph'],
    ];
@endphp
<nav class="bg-white border-bottom" aria-label="Admin">
    <ul class="nav container flex-nowrap overflow-auto ll-admin-nav">
        @foreach ($adminLinks as $link)
            @php($isActive = request()->routeIs(...explode('|', $link['match'])))
            <li class="nav-item">
                <a href="{{ route($link['route']) }}" class="nav-link text-nowrap {{ $isActive ? 'active' : '' }}"
                   @if ($isActive) aria-current="page" @endif>
                    <i class="bi {{ $link['icon'] }}" aria-hidden="true"></i> {{ $link['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
