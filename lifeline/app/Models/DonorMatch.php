<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonorMatch extends Model
{
    // status and responded_at change only through respond() (Task 3).
    protected $fillable = ['blood_request_id', 'donor_id', 'distance_km'];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'responded_at' => 'datetime',
        ];
    }

    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class);
    }
}
