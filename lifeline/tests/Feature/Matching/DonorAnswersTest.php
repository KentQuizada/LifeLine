<?php

namespace Tests\Feature\Matching;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\DonorMatch;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Donors answer their matches; contact details are shared only after acceptance (DEC-004, DEC-016). */
class DonorAnswersTest extends TestCase
{
    use RefreshDatabase;

    private Donor $donor;

    private BloodRequest $bloodRequest;

    private DonorMatch $match;

    protected function setUp(): void
    {
        parent::setUp();

        $this->donor = Donor::factory()->create([
            'blood_type' => 'O-',
            'verification_status' => 'verified',
            'is_available' => true,
            'location_sharing' => true,
        ]);
        $requester = User::factory()->create(['role' => 'requester', 'mobile_number' => '09171234567']);
        $this->bloodRequest = BloodRequest::submit($requester, [
            'hospital_id' => Hospital::factory()->create(['name' => 'Sample Answer Hospital', 'contact_number' => '085-111-2222'])->id,
            'patient_name' => 'Hidden Patient Name',
            'blood_type' => 'A+',
            'units' => 2,
            'urgency' => 'critical',
            'notes' => null,
        ]);
        $this->match = $this->bloodRequest->matches()->create(['donor_id' => $this->donor->id, 'distance_km' => 2.6]);
    }

    private function answer(string $answer, ?DonorMatch $match = null)
    {
        return $this->actingAs($this->donor->user)
            ->from(route('matches.index'))
            ->patch(route('matches.update', ($match ?? $this->match)->id), ['answer' => $answer]);
    }

    public function test_requests_for_you_shows_the_request_but_no_private_details_before_accepting(): void
    {
        $this->actingAs($this->donor->user)->get(route('matches.index'))
            ->assertOk()
            ->assertSee('Requests for you')
            ->assertSee('A+ blood')
            ->assertSee('Sample Answer Hospital')
            ->assertSee('about 3 km')
            ->assertSee('Waiting for your answer')
            ->assertSee('LifeLine only suggests potential donors.')
            ->assertDontSee('Hidden Patient Name')
            ->assertDontSee('09171234567');
    }

    public function test_a_donor_sees_only_their_own_matches(): void
    {
        $otherDonor = Donor::factory()->create();

        $this->actingAs($otherDonor->user)->get(route('matches.index'))
            ->assertOk()
            ->assertDontSee('Sample Answer Hospital')
            ->assertSee('No requests for you yet');
    }

    public function test_accepting_shares_contacts_and_marks_the_request_donor_found(): void
    {
        $this->answer('accepted')->assertRedirect(route('matches.index'))->assertSessionHas('status');

        $this->match->refresh();
        $this->assertSame('accepted', $this->match->status);
        $this->assertNotNull($this->match->responded_at);
        $this->assertSame('donor_found', $this->bloodRequest->fresh()->status);
        $this->assertDatabaseHas('request_history', [
            'blood_request_id' => $this->bloodRequest->id,
            'changed_by' => $this->donor->user_id,
            'old_status' => 'open',
            'new_status' => 'donor_found',
            'remarks' => 'A donor accepted',
        ]);

        $this->actingAs($this->donor->user)->get(route('matches.index'))
            ->assertSee('You accepted')
            ->assertSee('Hidden Patient Name')
            ->assertSee('09171234567')
            ->assertSee('085-111-2222');
    }

    public function test_a_second_acceptance_keeps_donor_found_without_another_history_row(): void
    {
        $this->answer('accepted');
        $secondDonor = Donor::factory()->create(['verification_status' => 'verified']);
        $secondMatch = $this->bloodRequest->matches()->create(['donor_id' => $secondDonor->id, 'distance_km' => 4]);

        $this->actingAs($secondDonor->user)->patch(route('matches.update', $secondMatch->id), ['answer' => 'accepted']);

        $this->assertSame('accepted', $secondMatch->fresh()->status);
        $this->assertSame('donor_found', $this->bloodRequest->fresh()->status);
        $this->assertSame(1, $this->bloodRequest->history()->where('new_status', 'donor_found')->count());
    }

    public function test_declining_while_donor_found_keeps_donor_found(): void
    {
        $this->answer('accepted');
        $secondDonor = Donor::factory()->create(['verification_status' => 'verified']);
        $secondMatch = $this->bloodRequest->matches()->create(['donor_id' => $secondDonor->id, 'distance_km' => 4]);

        $this->actingAs($secondDonor->user)->patch(route('matches.update', $secondMatch->id), ['answer' => 'declined']);

        $this->assertSame('declined', $secondMatch->fresh()->status);
        $this->assertSame('donor_found', $this->bloodRequest->fresh()->status);
        $this->assertSame(1, $this->bloodRequest->history()->where('new_status', 'donor_found')->count());
    }

    public function test_declining_leaves_the_request_open(): void
    {
        $this->answer('declined')->assertRedirect(route('matches.index'));

        $this->assertSame('declined', $this->match->fresh()->status);
        $this->assertSame('open', $this->bloodRequest->fresh()->status);
        $this->assertSame(1, $this->bloodRequest->history()->count());
    }

    public function test_a_match_can_be_answered_only_once(): void
    {
        $this->answer('declined');

        $this->answer('accepted')->assertSessionHas('error', 'You already answered this request.');

        $this->assertSame('declined', $this->match->fresh()->status);
        $this->assertSame('open', $this->bloodRequest->fresh()->status);
    }

    public function test_closed_requests_cannot_be_answered(): void
    {
        $this->bloodRequest->changeStatus('cancelled', null);

        $this->answer('accepted')->assertSessionHas('error', 'This request is no longer looking for donors.');

        $this->assertSame('notified', $this->match->fresh()->status);
        $this->assertNull($this->match->fresh()->responded_at);
        $this->actingAs($this->donor->user)->get(route('matches.index'))
            ->assertSee('This request is closed')
            ->assertSee('Closed')
            ->assertDontSee('Waiting for your answer')
            ->assertDontSee('I can donate');
    }

    public function test_an_unknown_answer_is_rejected(): void
    {
        $this->answer('maybe')->assertSessionHasErrors('answer');

        $this->assertSame('notified', $this->match->fresh()->status);
        $this->assertNull($this->match->fresh()->responded_at);
    }

    public function test_another_donors_match_gives_404(): void
    {
        $otherDonor = Donor::factory()->create();

        $this->actingAs($otherDonor->user)
            ->patch(route('matches.update', $this->match->id), ['answer' => 'accepted'])
            ->assertNotFound();

        $this->assertSame('notified', $this->match->fresh()->status);
    }

    public function test_home_shows_how_many_requests_wait_for_an_answer(): void
    {
        $this->actingAs($this->donor->user)->get(route('home'))
            ->assertSee('Requests for you')
            ->assertSee('1 waiting');

        $this->answer('declined');

        $this->actingAs($this->donor->user)->get(route('home'))
            ->assertSee('Requests for you')
            ->assertDontSee('1 waiting');
    }
}
