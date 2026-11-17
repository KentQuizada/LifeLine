@extends('layouts.app')

@section('title', 'Admin dashboard')
@section('container_class', '')

@section('content')
    <h1 class="h4 fw-bold mb-3">Dashboard</h1>

    @php
        $cards = [
            ['label' => 'Donors', 'value' => $stats['users_by_role']['donor'], 'icon' => 'bi-droplet', 'route' => 'admin.donors.index'],
            ['label' => 'Waiting for verification', 'value' => $stats['donors_by_status']['pending'], 'icon' => 'bi-patch-question', 'route' => 'admin.donors.pending'],
            ['label' => 'Searchable donors', 'value' => $stats['searchable_donors'], 'icon' => 'bi-geo-alt', 'route' => 'admin.donors.index'],
            ['label' => 'Requesters', 'value' => $stats['users_by_role']['requester'], 'icon' => 'bi-people', 'route' => 'admin.users.index'],
            ['label' => 'Blood requests', 'value' => $stats['total_requests'], 'icon' => 'bi-clipboard2-pulse', 'route' => 'admin.requests.index'],
            ['label' => 'Looking for donors', 'value' => $stats['requests_by_status']['open'], 'icon' => 'bi-hourglass-split', 'route' => 'admin.requests.index'],
            ['label' => 'Donors accepted', 'value' => $stats['matches_by_status']['accepted'], 'icon' => 'bi-hand-thumbs-up', 'route' => 'admin.requests.index'],
            ['label' => 'Fulfilled', 'value' => $stats['fulfilled_percent'].'%', 'icon' => 'bi-check2-circle', 'route' => 'admin.reports.index'],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($cards as $card)
            <div class="col-6 col-md-3">
                <a href="{{ route($card['route']) }}" class="card shadow-sm h-100 text-decoration-none text-body ll-stat-card">
                    <div class="card-body">
                        <i class="bi {{ $card['icon'] }} text-brand" aria-hidden="true"></i>
                        <div class="ll-stat-value mt-1">{{ $card['value'] }}</div>
                        <div class="small text-secondary">{{ $card['label'] }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div id="admin-dashboard" data-api="{{ route('api.admin.stats') }}" class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100"><div class="card-body">
                <h2 class="h6 fw-bold">Requests by status</h2>
                <div class="ll-chart"><canvas data-chart="status" role="img" aria-label="Doughnut chart of blood requests by status"></canvas></div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100"><div class="card-body">
                <h2 class="h6 fw-bold">Donors by blood type</h2>
                <div class="ll-chart"><canvas data-chart="blood" role="img" aria-label="Bar chart of donors by blood type"></canvas></div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm h-100"><div class="card-body">
                <h2 class="h6 fw-bold">Requests in the last 14 days</h2>
                <div class="ll-chart"><canvas data-chart="daily" role="img" aria-label="Line chart of blood requests per day"></canvas></div>
            </div></div>
        </div>
        <p class="small text-secondary mb-0" data-chart-status role="status"></p>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 fw-bold mb-0">Latest requests</h2>
        <a href="{{ route('admin.requests.index') }}" class="small">All requests</a>
    </div>
    @include('admin.requests.table', ['bloodRequests' => $recentRequests])
@endsection

@push('scripts')
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endpush
