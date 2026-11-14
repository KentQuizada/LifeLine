<?php

namespace Tests\Feature\Donors;

use App\Models\Donor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Who may appear in matching (M2.3) and on the map (M2.4). */
class SearchableDonorsTest extends TestCase
{
    use RefreshDatabase;

    private function donor(array $attributes = []): Donor
    {
        return Donor::factory()->create($attributes + [
            'verification_status' => 'verified',
            'is_available' => true,
            'location_sharing' => true,
        ]);
    }

    public function test_only_verified_available_location_sharing_donors_are_searchable(): void
    {
        $visible = $this->donor();
        $this->donor(['is_available' => false]);
        $this->donor(['location_sharing' => false]);
        $this->donor(['verification_status' => 'pending']);
        $this->donor(['verification_status' => 'rejected']);

        $this->assertSame([$visible->id], Donor::searchable()->pluck('id')->all());
    }
}
