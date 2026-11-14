<?php

namespace Tests\Feature\Matching;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** The requester's side of matching: runs on submit, Find more donors, and the matches API (DEC-015). */
class RequestMatchesTest extends TestCase
{
    use RefreshDatabase;

    private User $requester;

    private Hospital $hospital;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requester = User::factory()->create(['role' => 'requester']);
        $this->hospital = Hospital::factory()->create(['latitude' => 8.950000, 'longitude' => 125.540000]);
    }

    private function donor(float $longitude = 125.55, array $attributes = []): Donor
    {
        return Donor::factory()->create($attributes + [
            'blood_type' => 'O-',
            'approx_latitude' => 8.95,
            'approx_longitude' => $longitude,
            'verification_status' => 'verified',
            'is_available' => true,
            'location_sharing' => true,
        ]);
    }

    private function request(array $attributes = []): BloodRequest
    {
        return BloodRequest::factory()->create($attributes + [
            'requester_id' => $this->requester->id,
            'hospital_id' => $this->hospital->id,
            'blood_type' => 'O-',
            'status' => 'open',
        ]);
    }

    public function test_submitting_a_request_matches_donors_right_away(): void
    {
        $donor = $this->donor();

        $this->actingAs($this->requester)->post(route('requests.store'), [
            'patient_name' => 'Juan Dela Cruz',
            'blood_type' => 'O-',
            'units' => 1,
            'urgency' => 'urgent',
            'hospital_id' => $this->hospital->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('donor_matches', ['donor_id' => $donor->id, 'status' => 'notified']);
    }

    public function test_the_api_lists_matches_nearest_first_and_shares_contacts_only_after_acceptance(): void
    {
        $far = $this->donor(125.57);
        $near = $this->donor(125.55);
        $accepting = $this->donor(125.56, [
            'user_id' => User::factory()->create([
                'role' => 'donor', 'first_name' => 'Ana', 'last_name' => 'Reyes', 'mobile_number' => '09181112222',
            ])->id,
        ]);
        $bloodRequest = $this->request();
        $bloodRequest->matchDonors();
        $bloodRequest->matches()->where('donor_id', $accepting->id)->first()->respond('accepted');
        $bloodRequest->matches()->where('donor_id', $far->id)->first()->respond('declined');

        $response = $this->actingAs($this->requester)
            ->getJson(route('api.requests.matches', $bloodRequest->id))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.status', 'notified')
            ->assertJsonPath('data.0.status_label', 'Waiting for reply')
            ->assertJsonPath('data.0.blood_type', 'O-')
            ->assertJsonPath('data.0.distance_km', 1)
            ->assertJsonPath('data.0.donor', null)
            ->assertJsonPath('data.1.status', 'accepted')
            ->assertJsonPath('data.1.donor.name', 'Ana Reyes')
            ->assertJsonPath('data.1.donor.mobile_number', '09181112222')
            ->assertJsonPath('data.2.status', 'declined')
            ->assertJsonPath('data.2.donor', null);

        $json = $response->getContent();
        $this->assertStringNotContainsString('latitude', $json);
        $this->assertStringNotContainsString('longitude', $json);
        $this->assertStringNotContainsString($near->user->mobile_number, $json);
        $this->assertStringNotContainsString($far->user->mobile_number, $json);
    }

    public function test_the_api_needs_a_login(): void
    {
        $bloodRequest = $this->request();

        // No Accept header: /api/v1 still answers with JSON (bootstrap/app.php).
        $this->get("/api/v1/requests/{$bloodRequest->id}/matches")
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_the_api_hides_other_peoples_requests(): void
    {
        $bloodRequest = $this->request();

        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->getJson(route('api.requests.matches', $bloodRequest->id))
            ->assertNotFound();
    }

    public function test_the_api_is_closed_to_admins_and_unverified_emails(): void
    {
        $bloodRequest = $this->request();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->getJson(route('api.requests.matches', $bloodRequest->id))
            ->assertForbidden();

        $this->actingAs(User::factory()->unverified()->create(['role' => 'requester']))
            ->getJson(route('api.requests.matches', $bloodRequest->id))
            ->assertForbidden();
    }

    public function test_signed_in_api_routes_use_the_login_session_and_tracking_stays_stateless(): void
    {
        // DEC-015: actingAs() skips the session, so check the route middleware directly.
        $this->assertContains('web', Route::getRoutes()->getByName('api.requests.matches')->gatherMiddleware());
        $this->assertNotContains('web', Route::getRoutes()->getByName('api.requests.track')->gatherMiddleware());
    }

    public function test_find_more_donors_adds_donors_who_became_eligible(): void
    {
        $bloodRequest = $this->request();
        $bloodRequest->matchDonors();
        $newcomer = $this->donor(125.56);

        $this->actingAs($this->requester)
            ->post(route('requests.matches.store', $bloodRequest->id))
            ->assertRedirect(route('requests.show', $bloodRequest->id))
            ->assertSessionHas('status', 'Found 1 more potential donor(s).');

        $this->assertDatabaseHas('donor_matches', ['blood_request_id' => $bloodRequest->id, 'donor_id' => $newcomer->id]);

        $this->actingAs($this->requester)
            ->post(route('requests.matches.store', $bloodRequest->id))
            ->assertSessionHas('status', 'No new potential donors right now. Try again later.');
    }

    public function test_find_more_donors_is_refused_for_closed_requests(): void
    {
        $bloodRequest = $this->request(['status' => 'fulfilled']);
        $this->donor();

        $this->actingAs($this->requester)
            ->from(route('requests.show', $bloodRequest->id))
            ->post(route('requests.matches.store', $bloodRequest->id))
            ->assertSessionHas('error', 'This request is no longer looking for donors.');

        $this->assertSame(0, $bloodRequest->matches()->count());
    }

    public function test_find_more_donors_hides_other_peoples_requests(): void
    {
        $bloodRequest = $this->request();

        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->post(route('requests.matches.store', $bloodRequest->id))
            ->assertNotFound();
    }

    public function test_the_request_page_loads_potential_donors_from_the_api(): void
    {
        $bloodRequest = $this->request();

        $this->actingAs($this->requester)->get(route('requests.show', $bloodRequest->id))
            ->assertOk()
            ->assertSee('Potential donors')
            ->assertSee('data-api="'.route('api.requests.matches', $bloodRequest->id).'"', false)
            ->assertSee('js/matches.js')
            ->assertSee('Find more donors');

        $closed = $this->request(['status' => 'cancelled']);

        $this->actingAs($this->requester)->get(route('requests.show', $closed->id))
            ->assertDontSee('Find more donors');
    }
}
