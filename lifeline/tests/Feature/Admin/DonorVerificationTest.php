<?php

namespace Tests\Feature\Admin;

use App\Models\Donor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonorVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function donor(string $status = 'pending', array $userAttributes = []): Donor
    {
        return Donor::factory()
            ->for(User::factory()->state(['role' => 'donor'] + $userAttributes))
            ->create(['verification_status' => $status]);
    }

    public function test_admin_sees_pending_donors_only(): void
    {
        $this->donor('pending', ['first_name' => 'Pending', 'last_name' => 'Person']);
        $this->donor('verified', ['first_name' => 'Already', 'last_name' => 'Verified']);

        $this->actingAs($this->admin())
            ->get('/admin/donors/pending')
            ->assertOk()
            ->assertSee('Pending Person')
            ->assertDontSee('Already Verified');
    }

    public function test_admin_can_verify_a_donor(): void
    {
        $admin = $this->admin();
        $donor = $this->donor();

        $this->actingAs($admin)
            ->post("/admin/donors/{$donor->id}/verify")
            ->assertRedirect()
            ->assertSessionHas('status');

        $donor->refresh();
        $this->assertSame('verified', $donor->verification_status);
        $this->assertEquals($admin->id, $donor->verified_by);
        $this->assertNotNull($donor->verified_at);
    }

    public function test_admin_can_reject_a_donor(): void
    {
        $admin = $this->admin();
        $donor = $this->donor();

        $this->actingAs($admin)->post("/admin/donors/{$donor->id}/reject")->assertRedirect();

        $donor->refresh();
        $this->assertSame('rejected', $donor->verification_status);
        $this->assertEquals($admin->id, $donor->verified_by);
    }

    public function test_non_admins_cannot_verify_or_reject_donors(): void
    {
        $donor = $this->donor();

        foreach (['donor', 'requester'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->post("/admin/donors/{$donor->id}/verify")->assertForbidden();
            $this->actingAs($user)->post("/admin/donors/{$donor->id}/reject")->assertForbidden();
        }

        $this->assertSame('pending', $donor->fresh()->verification_status);
    }

    public function test_donor_home_shows_verification_status(): void
    {
        $donor = $this->donor('pending');
        $this->actingAs($donor->user)->get('/home')->assertOk()->assertSee('Waiting for verification');

        $donor->forceFill(['verification_status' => 'verified'])->save();
        $this->actingAs($donor->user->fresh())->get('/home')->assertOk()->assertSee('Verified donor');
    }

    public function test_requester_home_has_no_donor_status(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->get('/home')
            ->assertOk()
            ->assertDontSee('verification');
    }
}
