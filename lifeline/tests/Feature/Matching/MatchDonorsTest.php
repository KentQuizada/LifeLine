<?php

namespace Tests\Feature\Matching;

use App\Models\BloodRequest;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Hospital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Rule-based matching: compatible, eligible, nearest first (DEC-007, DEC-016). */
class MatchDonorsTest extends TestCase
{
    use RefreshDatabase;

    private Hospital $hospital;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-14 10:00:00');
        config(['lifeline.donation_interval_months' => 3, 'lifeline.match_limit' => 10]);
        $this->hospital = Hospital::factory()->create(['latitude' => 8.950000, 'longitude' => 125.540000]);
    }

    /** A searchable donor east of the hospital: each 0.01 degree of longitude is about 1.1 km here. */
    private function donor(string $bloodType, float $longitude = 125.55, array $attributes = []): Donor
    {
        return Donor::factory()->create($attributes + [
            'blood_type' => $bloodType,
            'approx_latitude' => 8.95,
            'approx_longitude' => $longitude,
            'verification_status' => 'verified',
            'is_available' => true,
            'location_sharing' => true,
        ]);
    }

    private function request(string $bloodType, array $attributes = []): BloodRequest
    {
        return BloodRequest::factory()->create($attributes + [
            'blood_type' => $bloodType,
            'hospital_id' => $this->hospital->id,
            'status' => 'open',
        ]);
    }

    private function matchedDonorIds(BloodRequest $bloodRequest): array
    {
        return $bloodRequest->matches()->orderByRaw('distance_km IS NULL, distance_km, donor_id')->pluck('donor_id')->all();
    }

    public function test_matches_compatible_donors_nearest_first(): void
    {
        $farUniversal = $this->donor('O-', 125.57);
        $nearSameType = $this->donor('A+', 125.55);
        $this->donor('B+', 125.55);   // incompatible with A+
        $this->donor('AB+', 125.55);  // incompatible with A+
        $bloodRequest = $this->request('A+');

        $added = $bloodRequest->matchDonors();

        $this->assertSame(2, $added);
        $this->assertSame([$nearSameType->id, $farUniversal->id], $this->matchedDonorIds($bloodRequest));
        $nearest = $bloodRequest->matches()->where('donor_id', $nearSameType->id)->first();
        $this->assertSame('notified', $nearest->status);
        $this->assertEqualsWithDelta(1.10, (float) $nearest->distance_km, 0.01);
    }

    public function test_only_verified_available_donors_are_matched(): void
    {
        $eligible = $this->donor('O+');
        $this->donor('O+', 125.55, ['is_available' => false]);
        $this->donor('O+', 125.55, ['verification_status' => 'pending']);
        $this->donor('O+', 125.55, ['verification_status' => 'rejected']);
        $bloodRequest = $this->request('O+');

        $bloodRequest->matchDonors();

        $this->assertSame([$eligible->id], $this->matchedDonorIds($bloodRequest));
    }

    public function test_donors_without_a_shared_location_are_matched_after_located_donors(): void
    {
        $far = $this->donor('O+', 125.60);
        $sharingOff = $this->donor('O+', 125.55, ['location_sharing' => false, 'approx_latitude' => null, 'approx_longitude' => null]);
        $noLocationYet = $this->donor('O+', 125.55, ['approx_latitude' => null, 'approx_longitude' => null]);
        $bloodRequest = $this->request('O+');

        $this->assertSame(3, $bloodRequest->matchDonors());

        // DEC-026: located donors first (nearest first), then unknown distances.
        $this->assertSame([$far->id, $sharingOff->id, $noLocationYet->id], $this->matchedDonorIds($bloodRequest));
        $this->assertNull($bloodRequest->matches()->where('donor_id', $sharingOff->id)->value('distance_km'));
        $this->assertNotNull($bloodRequest->matches()->where('donor_id', $far->id)->value('distance_km'));
    }

    public function test_the_match_limit_is_filled_with_located_donors_first(): void
    {
        config(['lifeline.match_limit' => 2]);
        $this->donor('O+', 125.55, ['approx_latitude' => null, 'approx_longitude' => null]);
        $near = $this->donor('O+', 125.55);
        $farther = $this->donor('O+', 125.60);
        $bloodRequest = $this->request('O+');

        $bloodRequest->matchDonors();

        $this->assertSame([$near->id, $farther->id], $this->matchedDonorIds($bloodRequest));
    }

    public function test_donors_who_gave_blood_within_the_interval_are_not_matched(): void
    {
        $onTheDay = $this->donor('O+', 125.55);
        Donation::create(['donor_id' => $onTheDay->id, 'donated_on' => '2026-08-14', 'is_self_reported' => true]);
        $tooRecent = $this->donor('O+', 125.56);
        Donation::create(['donor_id' => $tooRecent->id, 'donated_on' => '2026-08-15', 'is_self_reported' => true]);
        $bloodRequest = $this->request('O+');

        $bloodRequest->matchDonors();

        // Exactly 3 months ago (2026-08-14) is allowed; one day later is not.
        $this->assertSame([$onTheDay->id], $this->matchedDonorIds($bloodRequest));
    }

    public function test_the_donation_interval_comes_from_config(): void
    {
        config(['lifeline.donation_interval_months' => 6]);
        $donor = $this->donor('O+');
        Donation::create(['donor_id' => $donor->id, 'donated_on' => '2026-08-14', 'is_self_reported' => true]);
        $bloodRequest = $this->request('O+');

        $this->assertSame(0, $bloodRequest->matchDonors());
    }

    public function test_a_donor_is_never_matched_to_their_own_request(): void
    {
        $requesterDonor = $this->donor('O+');
        $other = $this->donor('O+', 125.56);
        $bloodRequest = $this->request('O+', ['requester_id' => $requesterDonor->user_id]);

        $bloodRequest->matchDonors();

        $this->assertSame([$other->id], $this->matchedDonorIds($bloodRequest));
    }

    public function test_only_the_nearest_donors_up_to_the_limit_are_matched(): void
    {
        config(['lifeline.match_limit' => 2]);
        $first = $this->donor('O-', 125.55);
        $second = $this->donor('O-', 125.56);
        $this->donor('O-', 125.57);
        $bloodRequest = $this->request('O-');

        $this->assertSame(2, $bloodRequest->matchDonors());
        $this->assertSame([$first->id, $second->id], $this->matchedDonorIds($bloodRequest));
    }

    public function test_running_again_adds_only_donors_not_matched_yet(): void
    {
        $first = $this->donor('B+');
        $bloodRequest = $this->request('B+');
        $this->assertSame(1, $bloodRequest->matchDonors());

        $newcomer = $this->donor('B-', 125.56);

        $this->assertSame(1, $bloodRequest->matchDonors());
        $this->assertSame(0, $bloodRequest->matchDonors());
        $this->assertSame([$first->id, $newcomer->id], $this->matchedDonorIds($bloodRequest));
    }

    public function test_donors_at_the_same_distance_are_matched_lowest_id_first(): void
    {
        config(['lifeline.match_limit' => 1]);
        $lower = $this->donor('O+', 125.55);
        $this->donor('O+', 125.55);
        $bloodRequest = $this->request('O+');

        $bloodRequest->matchDonors();

        $this->assertSame([$lower->id], $this->matchedDonorIds($bloodRequest));
    }

    public function test_already_matched_is_per_request(): void
    {
        $donor = $this->donor('O+');
        $requestA = $this->request('O+');
        $requestB = $this->request('O+');

        $requestA->matchDonors();

        $this->assertSame(1, $requestB->matchDonors());
        $this->assertSame([$donor->id], $this->matchedDonorIds($requestB));
    }

    public function test_closed_requests_are_not_matched(): void
    {
        $this->donor('O+');

        foreach (['fulfilled', 'cancelled', 'expired'] as $status) {
            $bloodRequest = $this->request('O+', ['status' => $status]);
            $this->assertSame(0, $bloodRequest->matchDonors(), $status);
            $this->assertSame(0, $bloodRequest->matches()->count(), $status);
        }
    }

    public function test_a_request_with_a_donor_found_still_gets_more_matches(): void
    {
        $donor = $this->donor('AB+');
        $bloodRequest = $this->request('AB+', ['status' => 'donor_found']);

        $bloodRequest->matchDonors();

        $this->assertSame([$donor->id], $this->matchedDonorIds($bloodRequest));
    }
}
