<?php

namespace Tests\Feature\Profile;

use App\Models\Donor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** M2.4: donors set an approximate location (about 1 km) in Edit profile (DEC-004). */
class DonorLocationTest extends TestCase
{
    use RefreshDatabase;

    private function donor(array $attributes = []): Donor
    {
        return Donor::factory()->create($attributes + [
            'blood_type' => 'O+',
            'verification_status' => 'verified',
            'location_sharing' => true,
            'approx_latitude' => null,
            'approx_longitude' => null,
        ]);
    }

    private function form(Donor $donor, array $overrides = []): array
    {
        return $overrides + [
            'first_name' => $donor->user->first_name,
            'last_name' => $donor->user->last_name,
            'mobile_number' => $donor->user->mobile_number,
            'blood_type' => $donor->blood_type,
            'birthdate' => $donor->birthdate->toDateString(),
            'weight_kg' => (float) $donor->weight_kg,
            'city' => $donor->city,
            'location_sharing' => '1',
        ];
    }

    public function test_the_edit_page_has_the_location_picker(): void
    {
        $donor = $this->donor();

        $this->actingAs($donor->user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Use my current location')
            ->assertSee('js/location-picker.js', false);
    }

    public function test_coordinates_are_rounded_to_about_one_kilometre_before_saving(): void
    {
        $donor = $this->donor();

        $this->actingAs($donor->user)->put(route('profile.update'), $this->form($donor, [
            'approx_latitude' => '8.947812',
            'approx_longitude' => '125.543219',
        ]))->assertRedirect(route('profile.show'));

        $donor->refresh();
        $this->assertSame('8.95', $donor->getRawOriginal('approx_latitude'));
        $this->assertSame('125.54', $donor->getRawOriginal('approx_longitude'));
    }

    public function test_locations_outside_the_philippines_are_rejected(): void
    {
        $donor = $this->donor();

        $this->actingAs($donor->user)->put(route('profile.update'), $this->form($donor, [
            'approx_latitude' => '35.68',
            'approx_longitude' => '139.69',
        ]))->assertSessionHasErrors(['approx_latitude', 'approx_longitude']);
    }

    public function test_latitude_without_longitude_is_rejected(): void
    {
        $donor = $this->donor();

        $this->actingAs($donor->user)->put(route('profile.update'), $this->form($donor, [
            'approx_latitude' => '8.95',
        ]))->assertSessionHasErrors('approx_longitude');
    }

    public function test_turning_sharing_off_drops_a_submitted_location(): void
    {
        $donor = $this->donor(['approx_latitude' => 8.95, 'approx_longitude' => 125.54]);
        $form = $this->form($donor, ['approx_latitude' => '8.96', 'approx_longitude' => '125.55']);
        unset($form['location_sharing']);

        $this->actingAs($donor->user)->put(route('profile.update'), $form);

        $this->assertNull($donor->refresh()->approx_latitude);
    }

    public function test_a_form_without_location_fields_keeps_the_saved_location(): void
    {
        $donor = $this->donor(['approx_latitude' => 8.95, 'approx_longitude' => 125.54]);

        $this->actingAs($donor->user)->put(route('profile.update'), $this->form($donor));

        $this->assertSame('8.95', $donor->refresh()->getRawOriginal('approx_latitude'));
    }

    public function test_home_asks_a_sharing_donor_without_a_location_to_set_one(): void
    {
        $donor = $this->donor();

        $this->actingAs($donor->user)->get(route('home'))->assertSee('Set your approximate location');
    }

    public function test_profile_does_not_claim_a_location_that_was_never_saved(): void
    {
        $withoutLocation = $this->donor();
        $withLocation = $this->donor(['approx_latitude' => 8.95, 'approx_longitude' => 125.54]);

        $this->actingAs($withoutLocation->user)->get(route('profile.show'))
            ->assertSee('On, but no location set yet')
            ->assertDontSee('On (about 1 km)');
        $this->actingAs($withLocation->user)->get(route('profile.show'))
            ->assertSee('On (about 1 km)')
            ->assertDontSee('On, but no location set yet');
    }
}
