<?php

namespace Tests\Feature\Donors;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function searchableDonor(): Donor
    {
        return Donor::factory()->create([
            'verification_status' => 'verified',
            'is_available' => true,
            'location_sharing' => true,
        ]);
    }

    public function test_turning_availability_off_and_on_changes_donor_searches(): void
    {
        $donor = $this->searchableDonor();

        $this->actingAs($donor->user)
            ->from('/home')
            ->patch('/availability', ['is_available' => '0'])
            ->assertRedirect('/home')
            ->assertSessionHas('status');
        $this->assertFalse($donor->fresh()->is_available);
        $this->assertSame(0, Donor::searchable()->count());

        $this->actingAs($donor->user)->from('/home')->patch('/availability', ['is_available' => '1']);
        $this->assertTrue($donor->fresh()->is_available);
        $this->assertSame(1, Donor::searchable()->count());
    }

    public function test_availability_value_is_validated(): void
    {
        $donor = $this->searchableDonor();

        foreach (['maybe', '', ['1']] as $value) {
            $this->actingAs($donor->user)
                ->from('/home')
                ->patch('/availability', ['is_available' => $value])
                ->assertSessionHasErrors('is_available');
        }
        $this->assertTrue($donor->fresh()->is_available);
    }

    public function test_only_donors_have_availability(): void
    {
        foreach (['requester', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->patch('/availability', ['is_available' => '0'])
                ->assertForbidden();
        }
    }

    public function test_donor_home_shows_availability_and_stats(): void
    {
        $donor = $this->searchableDonor();
        Donation::create(['donor_id' => $donor->id, 'donated_on' => '2026-05-04', 'is_self_reported' => true]);

        $this->actingAs($donor->user)
            ->get('/home')
            ->assertOk()
            ->assertSee('Available to donate')
            ->assertSee('Set unavailable')
            ->assertSee('Verified donor')
            ->assertSee($donor->blood_type)
            ->assertSee('Completed donations')
            ->assertSee('May 4, 2026')
            ->assertSee(route('donations.index'))
            ->assertSee(route('profile.show'));

        $donor->update(['is_available' => false]);
        $this->actingAs($donor->user->fresh())->get('/home')->assertSee('Not available right now')->assertSee('Set available');
    }

    public function test_donor_profile_shows_availability_switch(): void
    {
        $donor = $this->searchableDonor();

        $this->actingAs($donor->user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Set unavailable');
    }

    public function test_requester_home_has_no_donor_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester', 'first_name' => 'Jose']))
            ->get('/home')
            ->assertOk()
            ->assertSee('Hello, Jose!')
            ->assertDontSee('Availability')
            ->assertDontSee('Completed donations');
    }
}
