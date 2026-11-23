<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Donor extends Model
{
    use HasFactory;

    public const BLOOD_TYPES = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    // Verification fields are deliberately not fillable: only admin code may set them.
    protected $fillable = [
        'user_id',
        'blood_type',
        'birthdate',
        'weight_kg',
        'city',
        'approx_latitude',
        'approx_longitude',
        'location_sharing',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'weight_kg' => 'decimal:2',
            'approx_latitude' => 'decimal:2',
            'approx_longitude' => 'decimal:2',
            'location_sharing' => 'boolean',
            'is_available' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /** Last donation = MAX(donations.donated_on); it is not stored on donors (3NF). */
    public function lastDonationOn(): ?Carbon
    {
        $date = $this->donations()->max('donated_on');

        return $date ? Carbon::parse($date) : null;
    }

    /**
     * Donors that matching (M2.3) may suggest: verified by an admin and available. A shared location is
     * optional (DEC-026): it only puts the donor ahead of donors whose distance is unknown. A donor who
     * does not want requests switches "Available" off.
     */
    public function scopeSearchable(Builder $query): void
    {
        $query->where('verification_status', 'verified')
            ->where('is_available', true);
    }

    /** True when matching can measure this donor's distance (sharing on and a location saved). */
    public function hasSharedLocation(): bool
    {
        return $this->location_sharing && $this->approx_latitude !== null && $this->approx_longitude !== null;
    }

    /**
     * Potential donors for one request (DEC-016): searchable, compatible blood type, no donation within the
     * configured interval, not the requester, not matched yet. A location is not required (DEC-026).
     */
    public function scopeEligibleFor(Builder $query, BloodRequest $bloodRequest): void
    {
        $lastAllowedDonation = now()
            ->subMonthsNoOverflow(config('lifeline.donation_interval_months'))
            ->toDateString();

        $query->searchable()
            ->whereIn('blood_type', BloodRequest::COMPATIBLE_DONORS[$bloodRequest->blood_type])
            ->where('user_id', '!=', $bloodRequest->requester_id)
            ->whereDoesntHave('donations', fn (Builder $donations) => $donations->where('donated_on', '>', $lastAllowedDonation))
            ->whereDoesntHave('matches', fn (Builder $matches) => $matches->where('blood_request_id', $bloodRequest->id));
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DonorMatch::class);
    }
}
