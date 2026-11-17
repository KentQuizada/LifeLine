<?php

namespace Tests\Feature\Notifications;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** M2.5: the Alerts tab, its /api/v1/notifications endpoints and the email preference. */
class AlertsTabTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'requester']);
    }

    private function alert(User $user, array $attributes = []): Notification
    {
        return Notification::create($attributes + [
            'user_id' => $user->id,
            'title' => 'Donor match found!',
            'message' => 'A donor accepted your request.',
        ]);
    }

    public function test_the_alerts_page_loads_and_shows_the_unread_badge(): void
    {
        $this->alert($this->user);
        $this->alert($this->user);

        $this->actingAs($this->user)->get(route('alerts.index'))
            ->assertOk()
            ->assertSee(route('api.notifications.index'), false)
            ->assertSee('data-unread-badge', false)
            ->assertSee('2 unread');
    }

    public function test_the_api_lists_only_my_alerts_newest_first_with_the_unread_count(): void
    {
        $this->alert($this->user, ['title' => 'Older', 'read_at' => now()]);
        $this->alert($this->user, ['title' => 'Newer']);
        $this->alert(User::factory()->create(), ['title' => 'Someone else']);

        $this->actingAs($this->user)->getJson(route('api.notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Newer')
            ->assertJsonPath('data.0.read', false)
            ->assertJsonPath('data.0.group', 'today')
            ->assertJsonPath('data.1.read', true)
            ->assertDontSee('Someone else');
    }

    public function test_older_alerts_are_grouped_as_earlier(): void
    {
        $alert = $this->alert($this->user);
        $alert->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->actingAs($this->user)->getJson(route('api.notifications.index'))
            ->assertJsonPath('data.0.group', 'earlier');
    }

    public function test_mark_all_read_only_touches_my_alerts(): void
    {
        $mine = $this->alert($this->user);
        $theirs = $this->alert(User::factory()->create());

        $this->actingAs($this->user)->postJson(route('api.notifications.read'))
            ->assertOk()->assertJsonPath('unread_count', 0);

        $this->assertNotNull($mine->refresh()->read_at);
        $this->assertNull($theirs->refresh()->read_at);
    }

    public function test_the_api_uses_the_login_session_and_answers_guests_in_json(): void
    {
        $this->assertContains('web', Route::getRoutes()->getByName('api.notifications.read')->gatherMiddleware());
        $this->getJson(route('api.notifications.index'))->assertUnauthorized();
        $this->postJson(route('api.notifications.read'))->assertUnauthorized();
    }

    public function test_opening_a_request_alert_marks_it_read_and_goes_to_the_request(): void
    {
        $bloodRequest = BloodRequest::factory()->create(['requester_id' => $this->user->id]);
        $alert = $this->alert($this->user, ['blood_request_id' => $bloodRequest->id]);

        $this->actingAs($this->user)->get(route('alerts.open', $alert->id))
            ->assertRedirect(route('requests.show', $bloodRequest->id));

        $this->assertNotNull($alert->refresh()->read_at);
    }

    public function test_a_donor_opening_a_request_alert_goes_to_requests_for_you(): void
    {
        $donor = Donor::factory()->create();
        $alert = $this->alert($donor->user, ['blood_request_id' => BloodRequest::factory()->create()->id]);

        $this->actingAs($donor->user)->get(route('alerts.open', $alert->id))
            ->assertRedirect(route('matches.index'));
    }

    public function test_other_peoples_alerts_give_404(): void
    {
        $alert = $this->alert(User::factory()->create());

        $this->actingAs($this->user)->get(route('alerts.open', $alert->id))->assertNotFound();
        $this->assertNull($alert->refresh()->read_at);
    }

    public function test_users_can_turn_email_notifications_off_and_on(): void
    {
        $form = [
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'mobile_number' => $this->user->mobile_number,
            'notifications_enabled' => '0',
        ];

        $this->actingAs($this->user)->put(route('profile.update'), $form)->assertRedirect(route('profile.show'));
        $this->assertFalse($this->user->refresh()->notifications_enabled);

        $this->actingAs($this->user)->put(route('profile.update'), ['notifications_enabled' => '1'] + $form);
        $this->assertTrue($this->user->refresh()->notifications_enabled);
    }
}
