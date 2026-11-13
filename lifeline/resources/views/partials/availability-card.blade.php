{{-- Donor "available to donate" switch. Needs $donor. Used on home and my profile. --}}
<div class="card shadow-sm mb-3">
    <div class="card-body d-flex align-items-center justify-content-between gap-3">
        <div>
            <h2 class="h6 fw-bold mb-1">Availability</h2>
            @if ($donor->is_available)
                <p class="mb-0 text-success"><i class="bi bi-circle-fill" aria-hidden="true"></i> Available to donate</p>
            @else
                <p class="mb-0 text-secondary"><i class="bi bi-circle" aria-hidden="true"></i> Not available right now</p>
            @endif
        </div>
        <form method="POST" action="{{ route('availability.update') }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="is_available" value="{{ $donor->is_available ? 0 : 1 }}">
            <button type="submit" class="btn {{ $donor->is_available ? 'btn-outline-secondary' : 'btn-success' }}">
                {{ $donor->is_available ? 'Set unavailable' : 'Set available' }}
            </button>
        </form>
    </div>
</div>
