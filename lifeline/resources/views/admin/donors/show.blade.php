@extends('layouts.app')

@section('title', 'Donor record')
@section('container_class', '')

@section('content')
    @php($name = $donor->user->first_name.' '.$donor->user->last_name)
    <p class="mb-2"><a href="{{ route('admin.donors.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> All donors</a></p>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0">{{ $name }}</h1>
        <div class="d-flex gap-2">
            @if ($donor->verification_status !== 'verified')
                <form method="POST" action="{{ route('admin.donors.verify', $donor) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">Verify</button>
                </form>
            @endif
            @if ($donor->verification_status !== 'rejected')
                <form method="POST" action="{{ route('admin.donors.reject', $donor) }}" onsubmit="return confirm('Reject this donor?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-5">Verification</dt>
                        <dd class="col-7">
                            @include('admin.donors.status-badge', ['status' => $donor->verification_status])
                            @if ($reviewer && $donor->verified_at)
                                <span class="text-secondary">by {{ $reviewer->first_name }} {{ $reviewer->last_name }}, {{ $donor->verified_at->format('M j, Y') }}</span>
                            @endif
                        </dd>
                        <dt class="col-5">Email</dt>
                        <dd class="col-7 text-break">{{ $donor->user->email }} {{ $donor->user->hasVerifiedEmail() ? '(verified)' : '(not verified)' }}</dd>
                        <dt class="col-5">Mobile number</dt><dd class="col-7">{{ $donor->user->mobile_number }}</dd>
                        <dt class="col-5">Blood type</dt><dd class="col-7">{{ $donor->blood_type }}</dd>
                        <dt class="col-5">Age</dt><dd class="col-7">{{ $donor->birthdate->age }} years</dd>
                        <dt class="col-5">Weight</dt><dd class="col-7">{{ (float) $donor->weight_kg }} kg</dd>
                        <dt class="col-5">City</dt><dd class="col-7">{{ $donor->city }}</dd>
                        <dt class="col-5">Available</dt><dd class="col-7">{{ $donor->is_available ? 'Yes' : 'No' }}</dd>
                        <dt class="col-5">Location sharing</dt>
                        <dd class="col-7">{{ $donor->location_sharing ? ($donor->approx_latitude !== null ? 'On, approximate location set' : 'On, location not set') : 'Off' }}</dd>
                        <dt class="col-5">Registered</dt><dd class="col-7 mb-0">{{ $donor->created_at->format('M j, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <h2 class="h6 fw-bold">Donations ({{ $donor->donations->count() }})</h2>
            @if ($donor->donations->isEmpty())
                <p class="small text-secondary">No donations recorded.</p>
            @else
                <ul class="list-group shadow-sm mb-3">
                    @foreach ($donor->donations as $donation)
                        <li class="list-group-item small">
                            {{ $donation->donated_on->format('M j, Y') }} · {{ $donation->is_self_reported ? 'Self-reported' : 'Through LifeLine' }}
                        </li>
                    @endforeach
                </ul>
            @endif

            <h2 class="h6 fw-bold">Recent matches</h2>
            @if ($matches->isEmpty())
                <p class="small text-secondary">This donor has not been matched to a request.</p>
            @else
                <ul class="list-group shadow-sm mb-3">
                    @foreach ($matches as $match)
                        <li class="list-group-item d-flex justify-content-between gap-2 small">
                            <a href="{{ route('admin.requests.show', $match->bloodRequest) }}" class="font-monospace">{{ $match->bloodRequest->reference_number }}</a>
                            <span>{{ \App\Models\DonorMatch::STATUS_LABELS[$match->status] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
