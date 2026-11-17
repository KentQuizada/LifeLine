{{-- Donor verification badge. Needs $status. --}}
<span class="badge {{ ['pending' => 'text-bg-warning', 'verified' => 'text-bg-success', 'rejected' => 'text-bg-danger'][$status] }}">{{ ucfirst($status) }}</span>
