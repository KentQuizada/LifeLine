<?php

namespace Tests\Feature\Matching;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\DonorMatch;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;

/** DEC-022: design D3 donor profile, recording a donation through a match, and withdrawing an acceptance. */
class DonationAndWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private User $requester;

    private BloodRequest $bloodRequest;

    protected function setUp(): void
    {
        parent::setUp();
        NotificationFacade::fake();

        $this->requester = User::factory()->create(['role' => 'requester']);
        $hospital = Hospital::factory()->create(['name' => 'D3 Test Hospital', 'latitude' => 8.95, 'longitude' => 125.54]);
        $this->bloodRequest = BloodRequest::submit($this->requester, [
            'patient_name' => 'Juan Dela Cruz',
            'blood_type' => 'O-',
            'units' => 1,
            'urgency' => 'critical',
            'hospital_id' => $hospital->id,
        ]);
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
            'city' => 'Butuan City',
        ]);
    }

    /** A matched donor who accepted. */
    private function acceptedMatch(?Donor $donor = null): DonorMatch
    {
        $donor ??= $this->donor();
        $this->bloodRequest->matchDonors();
        $match = $this->bloodRequest->matches()->where('donor_id', $donor->id)->firstOrFail();
        $match->respond('accepted');

        return $match->refresh();
    }

    // --- D3 donor profile ---

    public function test_the_requester_sees_an_accepted_donors_profile(): void
    {
        $match = $this->acceptedMatch($this->donor(['first_name' => 'Ana', 'last_name' => 'Reyes', 'mobile_number' => '09179998888']));

        $this->actingAs($this->requester)->get(route('requests.donor', [$this->bloodRequest->id, $match->id]))
            ->assertOk()
            ->assertSee('Ana Reyes')
            ->assertSee('09179998888')
            ->assertSee('Verified donor')
            ->assertSee('Record donation')
            ->assertDontSee('125.55')
            ->assertDontSee('kg');
    }

    public function test_donors_who_did_not_accept_have_no_profile_page(): void
    {
        $this->donor();
        $this->bloodRequest->matchDonors();
        $waiting = $this->bloodRequest->matches()->firstOrFail();

        $this->actingAs($this->requester)->get(route('requests.donor', [$this->bloodRequest->id, $waiting->id]))
            ->assertNotFound();
    }

    public function test_other_users_cannot_open_the_profile(): void
    {
        $match = $this->acceptedMatch();

        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->get(route('requests.donor', [$this->bloodRequest->id, $match->id]))
            ->assertNotFound();
    }

    public function test_the_request_page_api_links_accepted_donors_to_their_profile(): void
    {
        $match = $this->acceptedMatch();

        $this->actingAs($this->requester)->getJson(route('api.requests.matches', $this->bloodRequest->id))
            ->assertJsonPath('data.0.donor.profile_url', route('requests.donor', [$this->bloodRequest->id, $match->id]));
    }

    // --- Recording a donation ---

    public function test_recording_a_donation_adds_it_to_the_donors_history_and_thanks_them(): void
    {
        $match = $this->acceptedMatch();

        $this->actingAs($this->requester)
            ->post(route('requests.donor.donation', [$this->bloodRequest->id, $match->id]), ['donated_on' => now()->toDateString()])
            ->assertRedirect(route('requests.donor', [$this->bloodRequest->id, $match->id]));

        $this->assertDatabaseHas('donations', [
            'donor_id' => $match->donor_id,
            'blood_request_id' => $this->bloodRequest->id,
            'hospital_id' => $this->bloodRequest->hospital_id,
            'is_self_reported' => false,
        ]);
        $this->assertDatabaseHas('notifications', ['user_id' => $match->donor->user_id, 'title' => 'Thank you for donating!']);

        // The donation interval now keeps this donor out of new matches.
        $this->assertFalse(Donor::eligibleFor($this->bloodRequest->replicate())->whereKey($match->donor_id)->exists());
    }

    public function test_a_donation_is_recorded_only_once(): void
    {
        $match = $this->acceptedMatch();
        $url = route('requests.donor.donation', [$this->bloodRequest->id, $match->id]);

        $this->actingAs($this->requester)->post($url, ['donated_on' => now()->toDateString()]);
        $this->actingAs($this->requester)->post($url, ['donated_on' => now()->toDateString()])
            ->assertSessionHas('error', 'This donation is already recorded.');

        $this->assertSame(1, $match->donor->donations()->count());
    }

    public function test_the_donation_date_must_be_between_the_request_and_today(): void
    {
        $match = $this->acceptedMatch();
        $url = route('requests.donor.donation', [$this->bloodRequest->id, $match->id]);

        $this->actingAs($this->requester)->post($url, ['donated_on' => now()->addDay()->toDateString()])
            ->assertSessionHasErrors('donated_on');
        $this->actingAs($this->requester)->post($url, ['donated_on' => now()->subMonth()->toDateString()])
            ->assertSessionHasErrors('donated_on');
    }

    public function test_a_donation_can_still_be_recorded_after_the_request_is_fulfilled(): void
    {
        $match = $this->acceptedMatch();
        $this->bloodRequest->refresh()->changeStatus('fulfilled', $this->requester);

        $this->actingAs($this->requester)
            ->post(route('requests.donor.donation', [$this->bloodRequest->id, $match->id]), ['donated_on' => now()->toDateString()])
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('error');

        $this->assertSame(1, $match->donor->donations()->count());
    }

    public function test_no_donation_for_a_cancelled_request(): void
    {
        $match = $this->acceptedMatch();
        $this->bloodRequest->refresh()->changeStatus('cancelled', $this->requester);

        $this->actingAs($this->requester)
            ->post(route('requests.donor.donation', [$this->bloodRequest->id, $match->id]), ['donated_on' => now()->toDateString()])
            ->assertSessionHas('error');

        $this->assertSame(0, $match->donor->donations()->count());
    }

    public function test_a_donation_recorded_through_a_request_cannot_be_deleted_by_the_donor(): void
    {
        $match = $this->acceptedMatch();
        $donation = $match->recordDonation(now()->toDateString());

        $this->actingAs($match->donor->user)->delete(route('donations.destroy', $donation->id))->assertForbidden();
    }

    // --- Withdrawing ---

    public function test_the_only_accepted_donor_withdrawing_reopens_the_request_and_tells_the_requester(): void
    {
        $match = $this->acceptedMatch($this->donor(['first_name' => 'Ana', 'last_name' => 'Reyes']));
        $this->assertSame('donor_found', $this->bloodRequest->refresh()->status);

        $this->actingAs($match->donor->user)->post(route('matches.withdraw', $match->id))
            ->assertRedirect(route('matches.index'));

        $this->assertSame('withdrawn', $match->refresh()->status);
        $this->assertSame('open', $this->bloodRequest->refresh()->status);
        $this->assertDatabaseHas('request_history', [
            'blood_request_id' => $this->bloodRequest->id, 'old_status' => 'donor_found', 'new_status' => 'open', 'remarks' => 'The donor withdrew',
        ]);
        $this->assertDatabaseHas('notifications', ['user_id' => $this->requester->id, 'title' => 'A donor withdrew']);
        // Only the withdrawal alert, not a second generic status alert.
        $this->assertDatabaseMissing('notifications', ['user_id' => $this->requester->id, 'title' => "Request {$this->bloodRequest->reference_number}: Looking for donors"]);
    }

    public function test_withdrawing_keeps_donor_found_when_another_donor_accepted(): void
    {
        $first = $this->acceptedMatch();
        $this->acceptedMatch($this->donor());

        $this->actingAs($first->donor->user)->post(route('matches.withdraw', $first->id));

        $this->assertSame('donor_found', $this->bloodRequest->refresh()->status);
    }

    public function test_the_requester_no_longer_sees_contact_details_after_a_withdrawal(): void
    {
        $match = $this->acceptedMatch();
        $match->withdraw();

        $this->actingAs($this->requester)->getJson(route('api.requests.matches', $this->bloodRequest->id))
            ->assertJsonPath('data.0.status', 'withdrawn')
            ->assertJsonPath('data.0.donor', null);
        $this->actingAs($this->requester)->get(route('requests.donor', [$this->bloodRequest->id, $match->id]))
            ->assertNotFound();
    }

    public function test_a_donor_cannot_withdraw_after_the_donation_is_recorded(): void
    {
        $match = $this->acceptedMatch();
        $match->recordDonation(now()->toDateString());

        $this->actingAs($match->donor->user)->post(route('matches.withdraw', $match->id))
            ->assertSessionHas('error', 'Your donation for this request is already recorded.');

        $this->assertSame('accepted', $match->refresh()->status);
    }

    public function test_only_accepted_matches_can_be_withdrawn_and_only_by_their_donor(): void
    {
        $this->donor();
        $this->bloodRequest->matchDonors();
        $waiting = $this->bloodRequest->matches()->firstOrFail();

        $this->actingAs($waiting->donor->user)->post(route('matches.withdraw', $waiting->id))
            ->assertSessionHas('error', 'Only an accepted request can be withdrawn.');
        $this->actingAs($this->donor()->user)->post(route('matches.withdraw', $waiting->id))
            ->assertNotFound();
    }

    public function test_a_withdrawn_donor_is_not_matched_again_for_the_same_request(): void
    {
        $match = $this->acceptedMatch();
        $match->withdraw();

        $this->assertSame(0, $this->bloodRequest->refresh()->matchDonors());
    }

    public function test_requesters_and_admins_still_cannot_reopen_a_request_themselves(): void
    {
        $this->acceptedMatch();

        $this->assertFalse($this->bloodRequest->refresh()->canChangeTo('open'));
        $this->expectException(\InvalidArgumentException::class);
        $this->bloodRequest->reopen($this->requester, 'Trying anyway');
    }
}
