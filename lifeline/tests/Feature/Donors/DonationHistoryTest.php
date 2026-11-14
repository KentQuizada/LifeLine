<?php

namespace Tests\Feature\Donors;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function donation(Donor $donor, string $date, bool $selfReported = true): Donation
    {
        return Donation::create(['donor_id' => $donor->id, 'donated_on' => $date, 'is_self_reported' => $selfReported]);
    }

    public function test_donor_sees_only_their_own_donations(): void
    {
        $donor = Donor::factory()->create();
        $other = Donor::factory()->create();
        $this->donation($donor, '2026-03-02');
        $this->donation($other, '2026-04-09');

        $this->actingAs($donor->user)
            ->get('/donations')
            ->assertOk()
            ->assertSee('Mar 2, 2026')
            ->assertDontSee('Apr 9, 2026');
    }

    public function test_empty_history_says_so(): void
    {
        $donor = Donor::factory()->create();

        $this->actingAs($donor->user)->get('/donations')->assertOk()->assertSee('No donations recorded yet.');
    }

    public function test_donor_adds_a_self_reported_donation(): void
    {
        $donor = Donor::factory()->create();
        $hospital = Hospital::factory()->create();
        $date = now()->subMonths(4)->toDateString();

        $this->actingAs($donor->user)
            ->post('/donations', ['donated_on' => $date, 'hospital_id' => $hospital->id])
            ->assertRedirect(route('donations.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('donations', [
            'donor_id' => $donor->id,
            'hospital_id' => $hospital->id,
            'donated_on' => $date,
            'is_self_reported' => true,
        ]);
        $this->assertSame($date, $donor->lastDonationOn()->toDateString());
    }

    public function test_hospital_is_optional(): void
    {
        $donor = Donor::factory()->create();

        $this->actingAs($donor->user)
            ->post('/donations', ['donated_on' => now()->subYear()->toDateString(), 'hospital_id' => ''])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('donations', ['donor_id' => $donor->id, 'hospital_id' => null]);
    }

    public function test_invalid_donation_dates_are_rejected(): void
    {
        $donor = Donor::factory()->create(['birthdate' => '2000-01-01']);
        $this->donation($donor, '2026-02-01');

        foreach ([now()->addDay()->toDateString(), '1999-12-31', '2026-02-01', '01/02/2026', ''] as $date) {
            $this->actingAs($donor->user)
                ->post('/donations', ['donated_on' => $date])
                ->assertSessionHasErrors('donated_on');
        }

        $this->assertDatabaseCount('donations', 1);
    }

    public function test_unknown_hospital_is_rejected(): void
    {
        $donor = Donor::factory()->create();

        $this->actingAs($donor->user)
            ->post('/donations', ['donated_on' => now()->subMonth()->toDateString(), 'hospital_id' => 999999])
            ->assertSessionHasErrors('hospital_id');
    }

    public function test_donor_removes_a_self_reported_donation(): void
    {
        $donor = Donor::factory()->create();
        $donation = $this->donation($donor, '2026-02-01');

        $this->actingAs($donor->user)
            ->delete("/donations/{$donation->id}")
            ->assertRedirect(route('donations.index'));

        $this->assertModelMissing($donation);
    }

    public function test_donor_cannot_remove_another_donors_donation(): void
    {
        $donor = Donor::factory()->create();
        $donation = $this->donation(Donor::factory()->create(), '2026-02-01');

        $this->actingAs($donor->user)->delete("/donations/{$donation->id}")->assertNotFound();

        $this->assertModelExists($donation);
    }

    public function test_donations_recorded_through_lifeline_cannot_be_removed(): void
    {
        $donor = Donor::factory()->create();
        $donation = $this->donation($donor, '2026-02-01', selfReported: false);

        $this->actingAs($donor->user)->delete("/donations/{$donation->id}")->assertForbidden();

        $this->assertModelExists($donation);
    }

    public function test_requesters_have_no_donation_history(): void
    {
        $user = User::factory()->create(['role' => 'requester']);

        $this->actingAs($user)->get('/donations')->assertForbidden();
        $this->actingAs($user)->post('/donations', ['donated_on' => '2026-01-01'])->assertForbidden();
    }

    public function test_huge_donation_id_returns_404(): void
    {
        $donor = Donor::factory()->create();

        $this->actingAs($donor->user)->delete('/donations/99999999999999999999')->assertNotFound();
    }

    public function test_another_donors_donation_date_does_not_block_mine(): void
    {
        $donor = Donor::factory()->create();
        $other = Donor::factory()->create();
        $date = now()->subMonths(2)->toDateString();
        $this->donation($other, $date);

        $this->actingAs($donor->user)
            ->post('/donations', ['donated_on' => $date])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('donations', ['donor_id' => $donor->id, 'donated_on' => $date]);
    }
}
