<?php

namespace Tests\Feature\Requests;

use App\Models\BloodRequest;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MyRequestsTest extends TestCase
{
    use RefreshDatabase;

    private function requester(): User
    {
        return User::factory()->create(['role' => 'requester']);
    }

    private function requestFor(User $user, string $patient = 'Juan Dela Cruz'): BloodRequest
    {
        return BloodRequest::submit($user, [
            'hospital_id' => Hospital::factory()->create()->id,
            'patient_name' => $patient,
            'blood_type' => 'A+',
            'units' => 3,
            'urgency' => 'urgent',
            'notes' => null,
        ]);
    }

    public function test_my_requests_lists_only_my_own_requests_newest_first(): void
    {
        $user = $this->requester();
        $older = $this->requestFor($user);
        $newer = $this->requestFor($user);
        $theirs = $this->requestFor($this->requester());

        $this->actingAs($user)->get('/requests')
            ->assertOk()
            ->assertSeeInOrder([$newer->reference_number, $older->reference_number])
            ->assertDontSee($theirs->reference_number);
    }

    public function test_empty_list_points_to_the_sos_form(): void
    {
        $this->actingAs($this->requester())->get('/requests')
            ->assertOk()
            ->assertSee('No requests yet')
            ->assertSee('href="'.route('requests.create').'"', false);
    }

    public function test_owner_sees_details_history_and_actions(): void
    {
        $user = $this->requester();
        $bloodRequest = $this->requestFor($user);

        $this->actingAs($user)->get("/requests/{$bloodRequest->id}")
            ->assertOk()
            ->assertSee($bloodRequest->reference_number)
            ->assertSee('Juan Dela Cruz')
            ->assertSee($bloodRequest->hospital->name)
            ->assertSee('Looking for donors')
            ->assertSee('Request submitted')
            ->assertDontSee('Request sent')
            ->assertSee('Mark fulfilled')
            ->assertSee('Cancel request');
    }

    public function test_other_users_requests_are_not_found(): void
    {
        $bloodRequest = $this->requestFor($this->requester());
        $intruder = $this->requester();

        $this->actingAs($intruder)->get("/requests/{$bloodRequest->id}")->assertNotFound();
        $this->actingAs($intruder)
            ->patch("/requests/{$bloodRequest->id}/status", ['status' => 'cancelled'])
            ->assertNotFound();

        $this->assertSame('open', $bloodRequest->fresh()->status);
    }

    public function test_owner_can_cancel_an_open_request(): void
    {
        $user = $this->requester();
        $bloodRequest = $this->requestFor($user);

        $this->actingAs($user)
            ->patch("/requests/{$bloodRequest->id}/status", ['status' => 'cancelled'])
            ->assertRedirect(route('requests.show', $bloodRequest->id))
            ->assertSessionHas('status', 'Your request was cancelled.');

        $this->assertSame('cancelled', $bloodRequest->fresh()->status);
        $this->assertDatabaseHas('request_history', [
            'blood_request_id' => $bloodRequest->id,
            'changed_by' => $user->id,
            'old_status' => 'open',
            'new_status' => 'cancelled',
            'remarks' => 'Cancelled by requester',
        ]);
    }

    public function test_owner_can_mark_a_request_fulfilled(): void
    {
        $user = $this->requester();
        $bloodRequest = $this->requestFor($user);
        $bloodRequest->changeStatus('donor_found', null, 'Donor accepted');

        $this->actingAs($user)
            ->patch("/requests/{$bloodRequest->id}/status", ['status' => 'fulfilled'])
            ->assertSessionHas('status', 'Your request was marked fulfilled.');

        $this->assertSame('fulfilled', $bloodRequest->fresh()->status);
        $this->assertDatabaseHas('request_history', [
            'blood_request_id' => $bloodRequest->id,
            'old_status' => 'donor_found',
            'new_status' => 'fulfilled',
        ]);
    }

    public function test_a_closed_request_cannot_be_changed(): void
    {
        $user = $this->requester();
        $bloodRequest = $this->requestFor($user);
        $bloodRequest->changeStatus('cancelled', $user);

        $this->actingAs($user)
            ->from("/requests/{$bloodRequest->id}")
            ->patch("/requests/{$bloodRequest->id}/status", ['status' => 'fulfilled'])
            ->assertRedirect("/requests/{$bloodRequest->id}")
            ->assertSessionHas('error', 'This request can no longer be changed.');

        $this->assertSame('cancelled', $bloodRequest->fresh()->status);
        $this->assertSame(2, $bloodRequest->history()->count());
    }

    public function test_closed_requests_hide_the_actions(): void
    {
        $user = $this->requester();
        $bloodRequest = $this->requestFor($user);
        $bloodRequest->changeStatus('cancelled', $user);

        $this->actingAs($user)->get("/requests/{$bloodRequest->id}")
            ->assertOk()
            ->assertSee('Cancelled')
            ->assertDontSee('Mark fulfilled')
            ->assertDontSee('Cancel request');
    }

    public static function statusesRequestersCannotSet(): array
    {
        return [
            'open' => ['open'],
            'donor found' => ['donor_found'],
            'expired' => ['expired'],
            'unknown' => ['approved'],
            'missing' => [''],
        ];
    }

    #[DataProvider('statusesRequestersCannotSet')]
    public function test_requesters_can_only_cancel_or_fulfil(string $status): void
    {
        $user = $this->requester();
        $bloodRequest = $this->requestFor($user);

        $this->actingAs($user)
            ->patch("/requests/{$bloodRequest->id}/status", ['status' => $status])
            ->assertSessionHasErrors('status');

        $this->assertSame('open', $bloodRequest->fresh()->status);
    }

    public function test_home_links_to_my_requests(): void
    {
        $this->actingAs($this->requester())->get('/home')
            ->assertOk()
            ->assertSee('href="'.route('requests.index').'"', false);
    }
}
