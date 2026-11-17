<?php

namespace Tests\Feature;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * M3.2: runs docs/testing/demo-script.md end to end on a freshly migrated and seeded database,
 * through the same routes the browser uses. If this passes, the demo has no server errors.
 */
class DemoScriptTest extends TestCase
{
    use RefreshDatabase;

    private function logIn(string $email): void
    {
        auth()->logout();
        $this->post(route('login'), ['login' => $email, 'password' => 'password'])->assertRedirect(route('home'));
    }

    public function test_the_demo_script_runs_end_to_end_on_a_fresh_database(): void
    {
        $this->seed();
        $hospital = Hospital::where('name', 'Sample General Hospital')->firstOrFail();

        // 1. Public tracking page works without an account.
        $this->get(route('track'))->assertOk();

        // 2. Requester: home, map, SOS request with a unique reference number.
        $this->logIn('requester@lifeline.test');
        $this->get(route('home'))->assertOk()->assertSee('Demo');
        $this->get(route('map'))->assertOk();
        $this->getJson(route('api.map', ['blood_type' => 'O-']))->assertOk()->assertJsonMissingPath('data.donors.0.name');
        $this->get(route('requests.create'))->assertOk();
        $this->post(route('requests.store'), [
            'patient_name' => 'Demo Patient',
            'blood_type' => 'AB+',
            'units' => 2,
            'urgency' => 'critical',
            'hospital_id' => $hospital->id,
        ])->assertRedirect();
        $bloodRequest = BloodRequest::latest('id')->firstOrFail();
        $this->get(route('requests.show', $bloodRequest->id))->assertOk()->assertSee($bloodRequest->reference_number);
        $this->getJson(route('api.requests.track', $bloodRequest->reference_number))->assertOk()->assertJsonPath('data.status', 'open');

        // 3. Donor: alert, Requests for you, accept.
        $demoDonor = Donor::whereHas('user', fn ($users) => $users->where('email', 'donor@lifeline.test'))->firstOrFail();
        $match = $bloodRequest->matches()->where('donor_id', $demoDonor->id)->firstOrFail();
        $this->logIn('donor@lifeline.test');
        $this->getJson(route('api.notifications.index'))->assertOk()->assertJsonPath('data.0.title', 'Critical request: AB+ blood needed');
        $this->get(route('alerts.index'))->assertOk();
        $this->get(route('matches.index'))->assertOk()->assertSee('AB+ blood · 2 unit(s)');
        $this->patch(route('matches.update', $match->id), ['answer' => 'accepted'])->assertRedirect(route('matches.index'));
        $this->postJson(route('api.notifications.read'))->assertOk();

        // 4. Requester: donor found, contact shared, alert received, then fulfilled.
        $this->logIn('requester@lifeline.test');
        $this->getJson(route('api.requests.matches', $bloodRequest->id))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Demo Donor', 'mobile_number' => '09170000002']);
        $this->getJson(route('api.notifications.index'))->assertJsonPath('data.0.title', 'Donor match found!');
        $this->patch(route('requests.status', $bloodRequest->id), ['status' => 'fulfilled'])->assertRedirect();
        $this->assertSame('fulfilled', $bloodRequest->refresh()->status);

        // 5. Admin: dashboard, verification, override a stale request, reports.
        $this->logIn('admin@lifeline.test');
        $this->get(route('admin.dashboard'))->assertOk();
        $this->getJson(route('api.admin.stats'))->assertOk()->assertJsonPath('data.requests_by_status.fulfilled', 1);
        $pending = Donor::whereHas('user', fn ($users) => $users->where('email', 'pending@lifeline.test'))->firstOrFail();
        $this->get(route('admin.donors.pending'))->assertOk()->assertSee('Pending Donor');
        $this->post(route('admin.donors.verify', $pending))->assertRedirect();
        $this->assertSame('verified', $pending->refresh()->verification_status);
        $stale = BloodRequest::where('status', 'open')->firstOrFail();
        $this->patch(route('admin.requests.status', $stale), ['status' => 'expired', 'remarks' => 'Demo: no longer needed'])->assertRedirect();
        $this->get(route('admin.requests.show', $stale))->assertOk()->assertSee('Demo: no longer needed');
        $this->get(route('admin.reports.index'))->assertOk();
        $this->get(route('admin.reports.requests'))->assertOk();

        // 6. Requester of the stale request was told about the admin change.
        $this->assertDatabaseHas('notifications', [
            'user_id' => $stale->requester_id,
            'title' => "Request {$stale->reference_number}: Expired",
        ]);
        $this->assertSame(1, User::where('email', 'requester@lifeline.test')->count());
    }
}
