<?php

namespace Tests\Feature\Requests;

use App\Models\BloodRequest;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Public tracking by reference number: status only, never patient or contact details (DEC-006). */
class TrackRequestTest extends TestCase
{
    use RefreshDatabase;

    private function trackedRequest(): BloodRequest
    {
        $bloodRequest = BloodRequest::submit(User::factory()->create(['role' => 'requester']), [
            'hospital_id' => Hospital::factory()->create(['name' => 'Sample Tracking Hospital'])->id,
            'patient_name' => 'Secret Patient Name',
            'blood_type' => 'B-',
            'units' => 4,
            'urgency' => 'critical',
            'notes' => 'Private note for donors',
        ]);
        $bloodRequest->changeStatus('donor_found', null, 'Private remark');

        return $bloodRequest;
    }

    public function test_tracking_returns_the_status_and_history_only(): void
    {
        $bloodRequest = $this->trackedRequest();

        $response = $this->getJson("/api/v1/requests/track/{$bloodRequest->reference_number}")
            ->assertOk()
            ->assertJsonPath('data.reference_number', $bloodRequest->reference_number)
            ->assertJsonPath('data.status', 'donor_found')
            ->assertJsonPath('data.status_label', 'Donor found')
            ->assertJsonPath('data.urgency', 'critical')
            ->assertJsonPath('data.blood_type', 'B-')
            ->assertJsonPath('data.hospital', 'Sample Tracking Hospital')
            ->assertJsonPath('data.history.0.status', 'open')
            ->assertJsonPath('data.history.1.status', 'donor_found');

        $this->assertSame(
            ['reference_number', 'status', 'status_label', 'urgency', 'blood_type', 'hospital', 'submitted_at', 'updated_at', 'history'],
            array_keys($response->json('data'))
        );
        $this->assertSame(['status', 'status_label', 'at'], array_keys($response->json('data.history.0')));
        $response->assertDontSee('Secret Patient Name')
            ->assertDontSee('Private note for donors')
            ->assertDontSee('Private remark')
            ->assertDontSee($bloodRequest->requester->email)
            ->assertDontSee($bloodRequest->requester->mobile_number);
    }

    public function test_reference_is_case_insensitive_and_trimmed(): void
    {
        $bloodRequest = $this->trackedRequest();

        $this->getJson('/api/v1/requests/track/'.rawurlencode(' '.strtolower($bloodRequest->reference_number).' '))
            ->assertOk()
            ->assertJsonPath('data.reference_number', $bloodRequest->reference_number);
    }

    public function test_unknown_reference_is_not_found(): void
    {
        $this->getJson('/api/v1/requests/track/LL-261114-ZZZZZZ')
            ->assertNotFound()
            ->assertJsonPath('message', 'No request found with that reference number.');
    }

    public static function malformedReferences(): array
    {
        return [
            'too short' => ['LL-261114-ABC'],
            'wrong prefix' => ['XX-261114-ABC234'],
            'letter in the date' => ['LL-26111A-ABC234'],
            'ambiguous letter O' => ['LL-261114-ABCO23'],
            'quote injection' => ["LL-261114' OR '1'='1"],
            'very long' => [str_repeat('A', 300)],
            'only whitespace' => [' '],
        ];
    }

    #[DataProvider('malformedReferences')]
    public function test_malformed_reference_is_rejected_as_json_without_an_accept_header(string $reference): void
    {
        $this->get('/api/v1/requests/track/'.rawurlencode($reference))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reference');
    }

    public function test_unknown_api_paths_answer_in_json(): void
    {
        $this->get('/api/v1/nothing-here')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_tracking_is_limited_to_thirty_lookups_a_minute(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->getJson('/api/v1/requests/track/LL-261114-ZZZZZZ')->assertNotFound();
        }

        $this->getJson('/api/v1/requests/track/LL-261114-ZZZZZZ')->assertTooManyRequests()
            ->assertJsonPath('message', 'Too Many Attempts.');
    }

    public function test_track_page_is_open_to_guests(): void
    {
        $this->get('/track')
            ->assertOk()
            ->assertSee('Track a request')
            ->assertSee('js/track.js')
            ->assertSee('data-api="'.url('/api/v1/requests/track').'"', false)
            ->assertDontSee('aria-label="Main"', false);
    }

    public function test_signed_in_users_get_the_track_page_in_the_app_layout(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->get('/track')
            ->assertOk()
            ->assertSee('aria-label="Main"', false);
    }

    public function test_login_page_and_request_page_link_to_tracking(): void
    {
        $this->get('/login')->assertSee('href="'.route('track').'"', false);

        $bloodRequest = $this->trackedRequest();
        $this->actingAs($bloodRequest->requester)
            ->get("/requests/{$bloodRequest->id}")
            ->assertSee(route('track', ['reference' => $bloodRequest->reference_number]), false);
    }
}
