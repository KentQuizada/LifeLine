<?php

namespace Tests\Feature\Notifications;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\LifeLineAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;

/** M2.5: who is notified, in-app and by email, when requests are matched, answered and closed (DEC-005, DEC-011). */
class EmergencyNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private User $requester;

    private Hospital $hospital;

    protected function setUp(): void
    {
        parent::setUp();
        NotificationFacade::fake();
        $this->requester = User::factory()->create(['role' => 'requester']);
        $this->hospital = Hospital::factory()->create(['latitude' => 8.95, 'longitude' => 125.54]);
    }

    private function donor(array $userAttributes = []): Donor
    {
        return Donor::factory()->create([
            'user_id' => User::factory()->create($userAttributes + ['role' => 'donor'])->id,
            'blood_type' => 'O-',
            'approx_latitude' => 8.95,
            'approx_longitude' => 125.55,
            'verification_status' => 'verified',
            'is_available' => true,
            'location_sharing' => true,
        ]);
    }

    private function submit(): BloodRequest
    {
        $this->actingAs($this->requester)->post(route('requests.store'), [
            'patient_name' => 'Juan Dela Cruz',
            'blood_type' => 'O-',
            'units' => 2,
            'urgency' => 'critical',
            'hospital_id' => $this->hospital->id,
        ])->assertRedirect();

        return BloodRequest::latest('id')->first();
    }

    public function test_a_new_request_notifies_each_matched_donor_in_app_and_by_email(): void
    {
        $donor = $this->donor();
        $bloodRequest = $this->submit();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $donor->user_id,
            'blood_request_id' => $bloodRequest->id,
            'title' => 'Critical request: O- blood needed',
        ]);
        NotificationFacade::assertSentTo($donor->user, LifeLineAlert::class,
            fn (LifeLineAlert $alert) => str_contains($alert->message, $this->hospital->name));
    }

    public function test_donors_who_turned_notifications_off_get_in_app_alerts_only(): void
    {
        $donor = $this->donor(['notifications_enabled' => false]);
        $this->submit();

        $this->assertDatabaseHas('notifications', ['user_id' => $donor->user_id]);
        NotificationFacade::assertNotSentTo($donor->user, LifeLineAlert::class);
    }

    public function test_find_more_donors_notifies_only_the_new_donors(): void
    {
        $first = $this->donor();
        $bloodRequest = $this->submit();
        $second = $this->donor();

        $this->actingAs($this->requester)->post(route('requests.matches.store', $bloodRequest->id));

        $this->assertSame(1, Notification::where('user_id', $first->user_id)->count());
        $this->assertSame(1, Notification::where('user_id', $second->user_id)->count());
    }

    public function test_an_acceptance_notifies_the_requester(): void
    {
        $donor = $this->donor(['first_name' => 'Ana', 'last_name' => 'Reyes']);
        $bloodRequest = $this->submit();
        $match = $bloodRequest->matches()->first();

        $this->actingAs($donor->user)->patch(route('matches.update', $match->id), ['answer' => 'accepted']);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->requester->id,
            'title' => 'Donor match found!',
        ]);
        NotificationFacade::assertSentTo($this->requester, LifeLineAlert::class,
            fn (LifeLineAlert $alert) => str_contains($alert->message, 'Ana Reyes'));
    }

    public function test_a_decline_does_not_notify_the_requester(): void
    {
        $donor = $this->donor();
        $bloodRequest = $this->submit();

        $this->actingAs($donor->user)->patch(route('matches.update', $bloodRequest->matches()->first()->id), ['answer' => 'declined']);

        $this->assertDatabaseMissing('notifications', ['user_id' => $this->requester->id]);
    }

    public function test_cancelling_notifies_waiting_and_accepted_donors_but_not_those_who_declined(): void
    {
        $waiting = $this->donor();
        $accepted = $this->donor();
        $declined = $this->donor();
        $bloodRequest = $this->submit();
        $bloodRequest->matches()->where('donor_id', $accepted->id)->first()->respond('accepted');
        $bloodRequest->matches()->where('donor_id', $declined->id)->first()->respond('declined');

        $this->actingAs($this->requester)->patch(route('requests.status', $bloodRequest->id), ['status' => 'cancelled']);

        $closed = "Request {$bloodRequest->reference_number} is closed";
        $this->assertDatabaseHas('notifications', ['user_id' => $waiting->user_id, 'title' => $closed]);
        $this->assertDatabaseHas('notifications', ['user_id' => $accepted->user_id, 'title' => $closed]);
        $this->assertDatabaseMissing('notifications', ['user_id' => $declined->user_id, 'title' => $closed]);
        // The requester made the change, so they are not told about it.
        $this->assertDatabaseMissing('notifications', ['user_id' => $this->requester->id, 'title' => "Request {$bloodRequest->reference_number}: Cancelled"]);
    }

    public function test_a_status_change_by_someone_else_notifies_the_requester(): void
    {
        $bloodRequest = $this->submit();
        $admin = User::factory()->create(['role' => 'admin']);

        $bloodRequest->changeStatus('expired', $admin, 'Expired by admin');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->requester->id,
            'title' => "Request {$bloodRequest->reference_number}: Expired",
        ]);
    }

    public function test_an_email_failure_never_blocks_the_request(): void
    {
        Exceptions::fake();
        NotificationFacade::shouldReceive('send')->andThrow(new \RuntimeException('SMTP down'));
        $donor = $this->donor();

        $bloodRequest = $this->submit();

        $this->assertNotNull($bloodRequest);
        $this->assertDatabaseHas('donor_matches', ['donor_id' => $donor->id]);
        $this->assertDatabaseHas('notifications', ['user_id' => $donor->user_id]);
        Exceptions::assertReported(\RuntimeException::class);
    }

    public function test_the_email_has_a_link_and_the_medical_disclaimer(): void
    {
        $mail = (new LifeLineAlert('Donor match found!', 'Ana accepted.'))->toMail($this->requester);

        $this->assertSame('LifeLine: Donor match found!', $mail->subject);
        $this->assertSame(route('alerts.index'), $mail->actionUrl);
        $this->assertStringContainsString('healthcare professionals', implode(' ', $mail->outroLines));
    }
}
