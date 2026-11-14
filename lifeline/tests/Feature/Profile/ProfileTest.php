<?php

namespace Tests\Feature\Profile;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_donor_sees_their_profile_and_medical_info(): void
    {
        $donor = Donor::factory()
            ->for(User::factory()->state([
                'role' => 'donor',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'mobile_number' => '09171234567',
            ]))
            ->create([
                'blood_type' => 'AB-',
                'city' => 'Butuan City',
                'birthdate' => now()->subYears(25)->subDay()->toDateString(),
                'verification_status' => 'verified',
            ]);
        Donation::create(['donor_id' => $donor->id, 'donated_on' => '2026-01-10', 'is_self_reported' => true]);
        Donation::create(['donor_id' => $donor->id, 'donated_on' => '2026-06-15', 'is_self_reported' => true]);

        $this->actingAs($donor->user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('AB-')
            ->assertSee('09171234567')
            ->assertSee($donor->user->email)
            ->assertSee('Butuan City')
            ->assertSee('25 years')
            ->assertSee('Verified donor')
            ->assertSee('Completed donations')
            ->assertSee('Jun 15, 2026')
            ->assertSee('Final compatibility and eligibility are confirmed by healthcare professionals.')
            ->assertSee(route('donations.index'));
    }

    public function test_donor_without_donations_sees_none_yet(): void
    {
        $donor = Donor::factory()->create();

        $this->actingAs($donor->user)->get('/profile')->assertOk()->assertSee('None yet');
    }

    public function test_requester_profile_has_no_medical_info(): void
    {
        $user = User::factory()->create(['role' => 'requester', 'first_name' => 'Jose']);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Jose')
            ->assertDontSee('Blood type')
            ->assertDontSee('Completed donations')
            ->assertDontSee(route('donations.index'));
    }

    public function test_admin_has_no_profile_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/profile')->assertForbidden();
    }

    public function test_donor_profile_has_account_menu_and_log_out(): void
    {
        $donor = Donor::factory()->create();

        $this->actingAs($donor->user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Account')
            ->assertSee('Medical info')
            ->assertSee(route('profile.edit').'#medical', false)
            ->assertSee('action="'.route('logout').'"', false);
    }

    public function test_requester_profile_has_no_medical_menu_item(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Medical info')
            ->assertSee('action="'.route('logout').'"', false);
    }

    public function test_profile_shows_location_sharing_state(): void
    {
        $on = Donor::factory()->create(['location_sharing' => true]);
        $off = Donor::factory()->create(['location_sharing' => false]);

        $this->actingAs($on->user)->get('/profile')->assertSee('On (about 1 km)');
        $this->actingAs($off->user)->get('/profile')->assertDontSee('On (about 1 km)')->assertSee('Off');
    }
}
