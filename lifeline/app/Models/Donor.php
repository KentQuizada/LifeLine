<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function matches(): HasMany
    {
        return $this->hasMany(DonorMatch::class);
    }
}
