<?php

namespace Tests\Feature\Profile;

use App\Models\Donor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditProfileTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedDonor(array $attributes = []): Donor
    {
        $admin = User::factory()->create(['role' => 'admin']);

        return Donor::factory()->create($attributes + [
            'blood_type' => 'O+',
            'verification_status' => 'verified',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'location_sharing' => true,
        ]);
    }

    private function payload(Donor $donor, array $overrides = []): array
    {
        return array_merge([
            'first_name' => $donor->user->first_name,
            'last_name' => $donor->user->last_name,
            'mobile_number' => $donor->user->mobile_number,
            'blood_type' => $donor->blood_type,
            'birthdate' => $donor->birthdate->toDateString(),
            'weight_kg' => (string) $donor->weight_kg,
            'city' => $donor->city,
            'location_sharing' => $donor->location_sharing ? '1' : '0',
        ], $overrides);
    }

    public function test_edit_page_shows_current_values(): void
    {
        $donor = $this->verifiedDonor(['city' => 'Butuan City']);

        $this->actingAs($donor->user)
            ->get('/profile/edit')
            ->assertOk()
            ->assertSee('value="'.$donor->user->mobile_number.'"', false)
            ->assertSee('Butuan City')
            ->assertSee($donor->user->email);
    }

    public function test_donor_updates_profile_and_medical_info(): void
    {
        $donor = $this->verifiedDonor();

        $this->actingAs($donor->user)
            ->put('/profile', $this->payload($donor, [
                'first_name' => 'Ana',
                'last_name' => 'Reyes',
                'mobile_number' => '+63 917 765 4321',
                'weight_kg' => '61.5',
                'city' => 'Cabadbaran City',
            ]))
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $donor->user_id,
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'mobile_number' => '09177654321',
        ]);
        $this->assertDatabaseHas('donors', ['id' => $donor->id, 'weight_kg' => 61.5, 'city' => 'Cabadbaran City']);
        $this->assertSame('verified', $donor->fresh()->verification_status);
    }

    public function test_keeping_your_own_mobile_number_is_allowed(): void
    {
        $donor = $this->verifiedDonor();

        $this->actingAs($donor->user)->put('/profile', $this->payload($donor))->assertSessionHasNoErrors();
    }

    public function test_mobile_number_of_another_user_is_rejected(): void
    {
        User::factory()->create(['mobile_number' => '09998887777']);
        $donor = $this->verifiedDonor();

        $this->actingAs($donor->user)
            ->put('/profile', $this->payload($donor, ['mobile_number' => '0999 888 7777']))
            ->assertSessionHasErrors('mobile_number');
    }

    public function test_email_role_and_verification_cannot_be_changed_here(): void
    {
        $donor = $this->verifiedDonor();
        $email = $donor->user->email;

        $this->actingAs($donor->user)->put('/profile', $this->payload($donor, [
            'email' => 'new@example.test',
            'role' => 'admin',
            'verification_status' => 'rejected',
        ]));

        $user = $donor->user->fresh();
        $this->assertSame($email, $user->email);
        $this->assertSame('donor', $user->role);
        $this->assertSame('verified', $donor->fresh()->verification_status);
    }

    public function test_changing_blood_type_sends_a_verified_donor_back_to_review(): void
    {
        $donor = $this->verifiedDonor();

        $this->actingAs($donor->user)
            ->put('/profile', $this->payload($donor, ['blood_type' => 'A-']))
            ->assertRedirect(route('profile.show'));

        $donor->refresh();
        $this->assertSame('A-', $donor->blood_type);
        $this->assertSame('pending', $donor->verification_status);
        $this->assertNull($donor->verified_by);
        $this->assertNull($donor->verified_at);
    }

    public function test_turning_location_sharing_off_clears_the_approximate_location(): void
    {
        $donor = $this->verifiedDonor(['approx_latitude' => 8.95, 'approx_longitude' => 125.54, 'is_available' => true]);
        $this->assertSame(1, Donor::searchable()->count());

        $payload = $this->payload($donor);
        unset($payload['location_sharing']); // an unticked checkbox sends nothing

        $this->actingAs($donor->user)->put('/profile', $payload)->assertRedirect(route('profile.show'));

        $donor->refresh();
        $this->assertFalse($donor->location_sharing);
        $this->assertNull($donor->approx_latitude);
        $this->assertNull($donor->approx_longitude);
        $this->assertSame(0, Donor::searchable()->count());
    }

    public function test_medical_info_is_validated(): void
    {
        $donor = $this->verifiedDonor();

        $this->actingAs($donor->user)
            ->put('/profile', $this->payload($donor, [
                'blood_type' => 'C+',
                'birthdate' => now()->addDay()->toDateString(),
                'weight_kg' => '0',
                'city' => '',
            ]))
            ->assertSessionHasErrors(['blood_type', 'birthdate', 'weight_kg', 'city']);
    }

    public function test_requester_updates_name_and_mobile_only(): void
    {
        $user = User::factory()->create(['role' => 'requester']);

        $this->actingAs($user)
            ->put('/profile', ['first_name' => 'Jose', 'last_name' => 'Rizal', 'mobile_number' => '09171112222', 'blood_type' => 'O+'])
            ->assertRedirect(route('profile.show'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'first_name' => 'Jose', 'mobile_number' => '09171112222']);
        $this->assertDatabaseCount('donors', 0);
    }

    public function test_array_input_is_rejected_without_a_server_error(): void
    {
        $donor = $this->verifiedDonor();

        $this->actingAs($donor->user)
            ->put('/profile', $this->payload($donor, ['first_name' => ['x'], 'mobile_number' => ['x'], 'weight_kg' => ['x']]))
            ->assertSessionHasErrors(['first_name', 'mobile_number', 'weight_kg']);
    }

    public function test_location_opt_out_survives_a_validation_error(): void
    {
        User::factory()->create(['mobile_number' => '09998887777']);
        $donor = $this->verifiedDonor();

        $payload = $this->payload($donor, ['mobile_number' => '09998887777']);
        unset($payload['location_sharing']); // an unticked checkbox sends nothing

        $response = $this->actingAs($donor->user)
            ->from('/profile/edit')
            ->followingRedirects()
            ->put('/profile', $payload)
            ->assertOk();

        $this->assertDoesNotMatchRegularExpression('/id="location_sharing"[^>]*checked/s', $response->getContent());
    }

    public function test_weight_accepts_two_decimals_and_rejects_three(): void
    {
        $donor = $this->verifiedDonor();

        $this->actingAs($donor->user)
            ->put('/profile', $this->payload($donor, ['weight_kg' => '61.37']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('donors', ['id' => $donor->id, 'weight_kg' => 61.37]);

        $this->actingAs($donor->user)
            ->put('/profile', $this->payload($donor, ['weight_kg' => '61.375']))
            ->assertSessionHasErrors('weight_kg');
    }

    public function test_changing_blood_type_sends_a_rejected_donor_back_to_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $donor = $this->verifiedDonor(['verification_status' => 'rejected', 'verified_by' => $admin->id, 'verified_at' => now()]);

        $this->actingAs($donor->user)->put('/profile', $this->payload($donor, ['blood_type' => 'A-']));

        $donor->refresh();
        $this->assertSame('pending', $donor->verification_status);
        $this->assertNull($donor->verified_by);
        $this->assertNull($donor->verified_at);
    }

    public function test_changing_blood_type_on_a_pending_donor_keeps_it_pending(): void
    {
        $donor = $this->verifiedDonor(['verification_status' => 'pending', 'verified_by' => null, 'verified_at' => null]);

        $this->actingAs($donor->user)
            ->put('/profile', $this->payload($donor, ['blood_type' => 'A-']))
            ->assertSessionHas('status', 'Your profile was updated.');

        $donor->refresh();
        $this->assertSame('A-', $donor->blood_type);
        $this->assertSame('pending', $donor->verification_status);
    }

    public function test_profile_updates_are_rate_limited(): void
    {
        $donor = $this->verifiedDonor();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($donor->user)->put('/profile', $this->payload($donor))->assertStatus(302);
        }

        $this->actingAs($donor->user)->put('/profile', $this->payload($donor))->assertStatus(429);
    }
}
