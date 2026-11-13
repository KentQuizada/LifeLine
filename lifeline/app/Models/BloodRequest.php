<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodRequest extends Model
{
    use HasFactory;

    public const STATUSES = ['open', 'donor_found', 'fulfilled', 'cancelled', 'expired'];

    protected $fillable = [
        'reference_number',
        'requester_id',
        'hospital_id',
        'patient_name',
        'blood_type',
        'units',
        'urgency',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'integer',
        ];
    }

    /** Format LL-YYMMDD-XXXXXX. Uniqueness is enforced by the database (DEC-006). */
    public static function generateReferenceNumber(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $suffix = '';

        for ($i = 0; $i < 6; $i++) {
            $suffix .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return 'LL-'.now()->format('ymd').'-'.$suffix;
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(RequestHistory::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DonorMatch::class);
    }
}
