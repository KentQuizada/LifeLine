<?php

namespace Tests\Feature\Requests;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** SOS form (design D1) and the request sent screen (D2). */
class SubmitRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validForm(array $overrides = []): array
    {
        return $overrides + [
            'patient_name' => 'Juan Dela Cruz',
            'blood_type' => 'O-',
            'units' => '2',
            'urgency' => 'critical',
            'hospital_id' => (string) Hospital::factory()->create()->id,
            'notes' => 'Surgery at 3 PM',
        ];
    }

    public static function appRoles(): array
    {
        return ['donor' => ['donor'], 'requester' => ['requester']];
    }

    public function test_sos_form_lists_hospitals_blood_types_urgency_and_the_disclaimer(): void
    {
        Hospital::factory()->create(['name' => 'Sample General Hospital']);

        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->get('/requests/new')
            ->assertOk()
            ->assertSee('Emergency blood request')
            ->assertSee('Sample General Hospital')
            ->assertSee('AB-')
            ->assertSee('Critical')
            ->assertSee('Urgent')
            ->assertSee('Final compatibility and eligibility are confirmed by healthcare professionals.');
    }

    #[DataProvider('appRoles')]
    public function test_donors_and_requesters_can_submit_a_request(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)->post('/requests', $this->validForm());

        $bloodRequest = BloodRequest::sole();
        $response->assertRedirect(route('requests.show', $bloodRequest->id));
        $this->assertSame($user->id, $bloodRequest->requester_id);
        $this->assertSame('open', $bloodRequest->status);
        $this->assertSame(
            ['Juan Dela Cruz', 'O-', 2, 'critical', 'Surgery at 3 PM'],
            [$bloodRequest->patient_name, $bloodRequest->blood_type, $bloodRequest->units, $bloodRequest->urgency, $bloodRequest->notes]
        );
        $this->assertSame(1, $bloodRequest->history()->count());
    }

    public function test_request_sent_screen_shows_the_reference_number(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->followingRedirects()
            ->post('/requests', $this->validForm())
            ->assertOk()
            ->assertSee('Request sent')
            ->assertSee(BloodRequest::sole()->reference_number)
            ->assertSee('Keep this reference number to track your request.')
            ->assertSee('Looking for donors');
    }

    public static function invalidForms(): array
    {
        return [
            'missing patient name' => ['patient_name', ''],
            'patient name too long' => ['patient_name', str_repeat('a', 151)],
            'missing blood type' => ['blood_type', ''],
            'unknown blood type' => ['blood_type', 'C+'],
            'zero units' => ['units', '0'],
            'eleven units' => ['units', '11'],
            'fractional units' => ['units', '1.5'],
            'unknown urgency' => ['urgency', 'low'],
            'missing hospital' => ['hospital_id', ''],
            'unknown hospital' => ['hospital_id', '999999'],
            'notes too long' => ['notes', str_repeat('a', 501)],
        ];
    }

    #[DataProvider('invalidForms')]
    public function test_invalid_requests_are_rejected(string $field, string $value): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->from('/requests/new')
            ->post('/requests', $this->validForm([$field => $value]))
            ->assertRedirect('/requests/new')
            ->assertSessionHasErrors($field);

        $this->assertSame(0, BloodRequest::count());
    }

    public function test_status_reference_and_requester_cannot_be_set_from_the_form(): void
    {
        $user = User::factory()->create(['role' => 'requester']);
        $other = User::factory()->create(['role' => 'requester']);

        $this->actingAs($user)->post('/requests', $this->validForm([
            'status' => 'fulfilled',
            'reference_number' => 'LL-261114-AAAAAA',
            'requester_id' => (string) $other->id,
        ]));

        $bloodRequest = BloodRequest::sole();
        $this->assertSame('open', $bloodRequest->status);
        $this->assertNotSame('LL-261114-AAAAAA', $bloodRequest->reference_number);
        $this->assertSame($user->id, $bloodRequest->requester_id);
    }

    public function test_submitting_is_limited_to_five_requests_per_ten_minutes(): void
    {
        $user = User::factory()->create(['role' => 'requester']);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)->post('/requests', $this->validForm())->assertRedirect();
        }

        $this->actingAs($user)->post('/requests', $this->validForm())->assertTooManyRequests();
        $this->assertSame(5, BloodRequest::count());
    }

    public function test_admin_cannot_submit_a_request(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/requests', $this->validForm())
            ->assertForbidden();

        $this->assertSame(0, BloodRequest::count());
    }

    public function test_home_has_the_emergency_request_button(): void
    {
        $requester = User::factory()->create(['role' => 'requester']);
        $donor = Donor::factory()->create();

        foreach ([$requester, $donor->user] as $user) {
            $this->actingAs($user)->get('/home')
                ->assertOk()
                ->assertSee('Emergency request')
                ->assertSee('href="'.route('requests.create').'"', false);
        }
    }
}
