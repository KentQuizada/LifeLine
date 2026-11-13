{{-- Donor "available to donate" switch (design C1 and E1). Needs $donor.
     The switch is a real submit button, so it works without JavaScript. --}}
<form method="POST" action="{{ route('availability.update') }}"
      class="card ll-availability shadow-sm mb-3 {{ $donor->is_available ? 'is-on' : '' }}">
    @csrf
    @method('PATCH')
    <input type="hidden" name="is_available" value="{{ $donor->is_available ? 0 : 1 }}">
    <div class="card-body d-flex align-items-center gap-3">
        <i class="bi {{ $donor->is_available ? 'bi-check-circle-fill' : 'bi-pause-circle text-secondary' }} fs-3" aria-hidden="true"></i>
        <div class="flex-grow-1">
            <h2 class="h6 fw-bold mb-0">{{ $donor->is_available ? 'Available to donate' : 'Not available right now' }}</h2>
            <p class="small mb-0 {{ $donor->is_available ? '' : 'text-secondary' }}">Tap the switch to change your status.</p>
        </div>
        <button type="submit" class="ll-switch {{ $donor->is_available ? 'is-on' : '' }}">
            <span class="visually-hidden">{{ $donor->is_available ? 'Set unavailable' : 'Set available' }}</span>
        </button>
    </div>
</form>
