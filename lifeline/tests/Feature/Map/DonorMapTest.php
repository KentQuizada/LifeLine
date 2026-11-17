<?php

namespace Tests\Feature\Map;

use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** M2.4: the donor map page and GET /api/v1/map never expose exact locations or identities (DEC-004). */
class DonorMapTest extends TestCase
{
    use RefreshDatabase;

    private User $requester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requester = User::factory()->create(['role' => 'requester']);
    }

    private function donor(array $attributes = []): Donor
    {
        return Donor::factory()->create($attributes + [
            'blood_type' => 'O+',
            'approx_latitude' => 8.95,
            'approx_longitude' => 125.55,
            'verification_status' => 'verified',
            'is_available' => true,
            'location_sharing' => true,
        ]);
    }

    public function test_the_map_page_loads_for_donors_and_requesters(): void
    {
        $this->actingAs($this->requester)->get(route('map'))
            ->assertOk()
            ->assertSee('Donor map')
            ->assertSee(route('api.map'), false)
            ->assertSee('vendor/leaflet/leaflet.js', false);

        $this->actingAs($this->donor()->user)->get(route('map'))->assertOk();
    }

    public function test_guests_and_admins_cannot_use_the_map(): void
    {
        $this->get(route('map'))->assertRedirect(route('login'));
        $this->getJson(route('api.map'))->assertUnauthorized();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->getJson(route('api.map'))->assertForbidden();
    }

    public function test_the_map_api_uses_the_login_session(): void
    {
        $this->assertContains('web', Route::getRoutes()->getByName('api.map')->gatherMiddleware());
    }

    public function test_only_searchable_donors_with_coordinates_are_listed(): void
    {
        $this->donor();
        $this->donor(['is_available' => false]);
        $this->donor(['verification_status' => 'pending']);
        $this->donor(['location_sharing' => false]);
        $this->donor(['approx_latitude' => null, 'approx_longitude' => null]);

        $this->actingAs($this->requester)->getJson(route('api.map'))
            ->assertOk()
            ->assertJsonCount(1, 'data.donors');
    }

    public function test_donors_never_see_themselves(): void
    {
        $self = $this->donor();

        $this->actingAs($self->user)->getJson(route('api.map'))->assertJsonCount(0, 'data.donors');
    }

    public function test_responses_hold_only_approximate_location_and_no_identity(): void
    {
        $donor = $this->donor();
        $donor->user->update(['first_name' => 'Secretname', 'mobile_number' => '09171234567']);

        $response = $this->actingAs($this->requester)->getJson(route('api.map'))->assertOk();

        $listed = $response->json('data.donors.0');
        $this->assertSame(['blood_type', 'latitude', 'longitude', 'distance_km'], array_keys($listed));
        $this->assertSame(round($listed['latitude'], 2), $listed['latitude']);
        $this->assertSame(round($listed['longitude'], 2), $listed['longitude']);
        $this->assertIsInt($listed['distance_km']);
        $response->assertDontSee('Secretname')->assertDontSee('09171234567')->assertDontSee($donor->user->email);
    }

    public function test_blood_type_filter_and_nearest_first(): void
    {
        $this->donor(['approx_longitude' => 125.60]);
        $this->donor(['approx_longitude' => 125.55]);
        $this->donor(['blood_type' => 'A-']);

        $response = $this->actingAs($this->requester)->getJson(route('api.map', [
            'blood_type' => 'O+', 'latitude' => 8.95, 'longitude' => 125.54,
        ]))->assertOk();

        $response->assertJsonCount(2, 'data.donors')
            ->assertJsonPath('data.center.source', 'user')
            ->assertJsonPath('data.donors.0.distance_km', 1)
            ->assertJsonPath('data.donors.1.distance_km', 7);
    }

    public function test_it_falls_back_to_the_default_centre_when_location_is_unknown(): void
    {
        $this->actingAs($this->requester)->getJson(route('api.map'))
            ->assertOk()
            ->assertJsonPath('data.center.source', 'default')
            ->assertJsonPath('data.center.latitude', 8.95);
    }

    public function test_hospitals_are_listed_with_distance(): void
    {
        Hospital::factory()->create(['name' => 'A Far Hospital', 'latitude' => 8.95, 'longitude' => 125.60]);
        Hospital::factory()->create(['name' => 'Sample Map Hospital', 'latitude' => 8.95, 'longitude' => 125.54]);

        $this->actingAs($this->requester)->getJson(route('api.map'))
            ->assertJsonPath('data.hospitals.0.name', 'Sample Map Hospital')
            ->assertJsonPath('data.hospitals.0.distance_km', 1)
            ->assertJsonPath('data.hospitals.1.name', 'A Far Hospital');
    }

    public function test_invalid_filters_are_rejected_as_json(): void
    {
        $this->actingAs($this->requester)->getJson(route('api.map', ['blood_type' => 'Z+']))
            ->assertUnprocessable()->assertJsonValidationErrors('blood_type');
        $this->actingAs($this->requester)->getJson(route('api.map', ['latitude' => 8.95]))
            ->assertUnprocessable()->assertJsonValidationErrors('longitude');
    }
}
