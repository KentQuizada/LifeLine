<?php

namespace Tests\Feature\Admin;

use App\Models\BloodRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** M2.6: the admin status override is audited in request_history and notifies the requester (DEC-014). */
class RequestOverrideTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_an_admin_can_mark_a_stale_request_expired_with_history_and_a_notification(): void
    {
        $bloodRequest = BloodRequest::factory()->create(['status' => 'open']);

        $this->actingAs($this->admin)->patch(route('admin.requests.status', $bloodRequest), [
            'status' => 'expired',
            'remarks' => 'No answer for a week',
        ])->assertRedirect(route('admin.requests.show', $bloodRequest));

        $this->assertSame('expired', $bloodRequest->refresh()->status);
        $this->assertDatabaseHas('request_history', [
            'blood_request_id' => $bloodRequest->id,
            'changed_by' => $this->admin->id,
            'old_status' => 'open',
            'new_status' => 'expired',
            'remarks' => 'Admin: No answer for a week',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $bloodRequest->requester_id,
            'title' => "Request {$bloodRequest->reference_number}: Expired",
        ]);
    }

    public function test_remarks_are_required_and_donor_found_cannot_be_forced(): void
    {
        $bloodRequest = BloodRequest::factory()->create(['status' => 'open']);

        $this->actingAs($this->admin)->patch(route('admin.requests.status', $bloodRequest), ['status' => 'expired'])
            ->assertSessionHasErrors('remarks');
        $this->actingAs($this->admin)->patch(route('admin.requests.status', $bloodRequest), ['status' => 'donor_found', 'remarks' => 'x'])
            ->assertSessionHasErrors('status');

        $this->assertSame('open', $bloodRequest->refresh()->status);
    }

    public function test_closed_requests_cannot_be_changed(): void
    {
        $bloodRequest = BloodRequest::factory()->create(['status' => 'cancelled']);

        $this->actingAs($this->admin)->from(route('admin.requests.show', $bloodRequest))
            ->patch(route('admin.requests.status', $bloodRequest), ['status' => 'fulfilled', 'remarks' => 'Try'])
            ->assertSessionHas('error');

        $this->assertSame('cancelled', $bloodRequest->refresh()->status);
        $this->actingAs($this->admin)->get(route('admin.requests.show', $bloodRequest))->assertSee('This request is closed');
    }

    public function test_non_admins_cannot_override(): void
    {
        $bloodRequest = BloodRequest::factory()->create(['status' => 'open']);

        $this->actingAs($bloodRequest->requester)
            ->patch(route('admin.requests.status', $bloodRequest), ['status' => 'expired', 'remarks' => 'x'])
            ->assertForbidden();
        $this->assertSame('open', $bloodRequest->refresh()->status);
    }
}
