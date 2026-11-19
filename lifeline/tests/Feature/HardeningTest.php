<?php

namespace Tests\Feature;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\DonorMatch;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** M3.2: fixes for the carry-forwards recorded in the system files (auth, matching, donors, requests). */
class HardeningTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedDonor(array $attributes = []): Donor
    {
        return Donor::factory()->create($attributes + [
            'blood_type' => 'O-',
            'approx_latitude' => 8.95,
            'approx_longitude' => 125.55,
            'verification_status' => 'verified',
            'is_available' => true,
            'location_sharing' => true,
        ]);
    }

    private function openRequest(): BloodRequest
    {
        $hospital = Hospital::factory()->create(['latitude' => 8.95, 'longitude' => 125.54]);

        return BloodRequest::submit(User::factory()->create(), [
            'patient_name' => 'Juan Dela Cruz',
            'blood_type' => 'O-',
            'units' => 1,
            'urgency' => 'urgent',
            'hospital_id' => $hospital->id,
        ]);
    }

    // --- Throttles (TASK-001) ---

    public function test_each_limited_route_has_its_own_counter(): void
    {
        $routes = [
            'password.email' => 'throttle:6,1,password-email',
            'password.update' => 'throttle:6,1,password-reset',
            'verification.verify' => 'throttle:6,1,verify-email',
            'verification.send' => 'throttle:6,1,verification-send',
            'profile.update' => 'throttle:10,1,profile-update',
        ];

        foreach ($routes as $name => $throttle) {
            $this->assertContains($throttle, Route::getRoutes()->getByName($name)->gatherMiddleware(), $name);
        }
    }

    public function test_forgot_password_requests_do_not_use_up_the_reset_limit(): void
    {
        Notification::fake();

        foreach (range(1, 6) as $attempt) {
            $this->post(route('password.email'), ['email' => 'nobody@example.test'])->assertRedirect();
        }

        // A separate counter: the reset form still answers normally (no 429).
        $this->post(route('password.update'), [
            'token' => 'invalid',
            'email' => 'nobody@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors('email');
    }

    // --- Login ---

    public function test_login_accepts_an_email_typed_in_capitals(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.test']);

        $this->post(route('login'), ['login' => 'ANA@Example.TEST', 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_one_network_cannot_try_many_accounts(): void
    {
        foreach (range(1, 30) as $attempt) {
            $this->post(route('login'), ['login' => "user{$attempt}@example.test", 'password' => 'wrong']);
        }

        $this->from(route('login'))
            ->post(route('login'), ['login' => 'another@example.test', 'password' => 'wrong'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['login' => 'Too many login attempts from this network. Try again in a minute.']);
    }

    // --- Password reset ---

    public function test_a_reset_link_can_be_requested_and_used_with_a_capitalised_email(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.test']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'Ana@Example.test',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertRedirect(route('login'));

        $this->post(route('login'), ['login' => 'ana@example.test', 'password' => 'newpass123'])
            ->assertRedirect(route('home'));
    }

    public function test_changing_the_password_ends_other_sessions(): void
    {
        $user = User::factory()->create(['email' => 'ana@example.test']);

        $this->post(route('login'), ['login' => 'ana@example.test', 'password' => 'password']);
        $this->get(route('profile.show'))->assertOk();

        // The password changes elsewhere (for example a reset on another phone).
        Auth::user()->forceFill(['password' => 'changed123'])->save();

        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertTrue($user->refresh()->exists);
    }

    // --- Registration ---

    public function test_resubmitting_step_one_with_the_same_role_keeps_the_medical_answers(): void
    {
        $step1 = ['role' => 'donor', 'first_name' => 'Maria', 'last_name' => 'Santos',
            'email' => 'maria@example.test', 'mobile_number' => '09171234567'];
        $this->post(route('register.step1'), $step1);
        $this->post(route('register.step2'), ['blood_type' => 'AB-', 'birthdate' => '1998-05-20',
            'weight_kg' => '58.5', 'city' => 'Butuan City']);

        $this->post(route('register.step1'), ['first_name' => 'Marie'] + $step1);
        $this->get(route('register.step2'))->assertOk()->assertSee('value="AB-" required checked', false);

        $this->post(route('register.step1'), ['role' => 'requester'] + $step1);
        $this->assertArrayNotHasKey('blood_type', session('registration'));
    }

    public function test_a_duplicate_saved_at_the_last_moment_sends_the_user_back_to_step_one(): void
    {
        $step1 = ['role' => 'requester', 'first_name' => 'Maria', 'last_name' => 'Santos',
            'email' => 'maria@example.test', 'mobile_number' => '09171234567'];
        $this->post(route('register.step1'), $step1);

        // Another sign-up with the same email lands between the re-check and the insert.
        User::creating(function (User $user) {
            if ($user->email === 'maria@example.test') {
                DB::table('users')->insert(User::factory()->raw(['email' => 'maria@example.test']));
            }
        });

        $this->post(route('register.step3'), ['password' => 'secret123', 'password_confirmation' => 'secret123',
            'privacy_consent' => '1', 'terms' => '1'])
            ->assertRedirect(route('register.step1'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // --- Admin review ---

    public function test_an_admin_cannot_reject_a_donor_who_is_already_verified(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $donor = $this->verifiedDonor();

        $this->actingAs($admin)->post(route('admin.donors.reject', $donor))
            ->assertSessionHas('error');

        $this->assertSame('verified', $donor->refresh()->verification_status);
    }

    // --- Matching ---

    public function test_a_closed_request_gets_no_new_matches_even_from_a_stale_copy(): void
    {
        $this->verifiedDonor();
        $bloodRequest = $this->openRequest();
        $stale = BloodRequest::find($bloodRequest->id); // loaded while still open

        $bloodRequest->changeStatus('cancelled', $bloodRequest->requester);
        $this->verifiedDonor();

        $this->assertSame(0, $stale->matchDonors());
    }

    public function test_a_donor_who_is_no_longer_verified_cannot_accept(): void
    {
        $donor = $this->verifiedDonor();
        $bloodRequest = $this->openRequest();
        $bloodRequest->matchDonors();
        $match = DonorMatch::where('donor_id', $donor->id)->firstOrFail();
        $donor->forceFill(['verification_status' => 'rejected'])->save();

        $this->actingAs($donor->user)->patch(route('matches.update', $match->id), ['answer' => 'accepted'])
            ->assertSessionHas('error', 'Only verified donors can accept requests.');

        $this->assertSame('notified', $match->refresh()->status);
        $this->assertSame('open', $bloodRequest->refresh()->status);
    }

    public function test_requests_for_you_is_paginated(): void
    {
        $donor = $this->verifiedDonor();
        foreach (range(1, 16) as $i) {
            BloodRequest::factory()->create()->matches()->create(['donor_id' => $donor->id, 'distance_km' => 1]);
        }

        $this->actingAs($donor->user)->get(route('matches.index'))
            ->assertOk()
            ->assertSee('page=2', false);
    }

    // --- Donors ---

    public function test_a_pending_donor_switching_available_is_told_they_are_not_searchable_yet(): void
    {
        $donor = $this->verifiedDonor(['verification_status' => 'pending', 'is_available' => false]);

        $this->actingAs($donor->user)->patch(route('availability.update'), ['is_available' => '1'])
            ->assertSessionHas('status', 'You are marked available. You will appear in donor searches once an admin verifies your account.');
    }

    public function test_a_donor_without_location_sharing_is_told_to_turn_it_on(): void
    {
        $donor = $this->verifiedDonor(['location_sharing' => false, 'is_available' => false]);

        $this->actingAs($donor->user)->patch(route('availability.update'), ['is_available' => '1'])
            ->assertSessionHas('status', 'You are marked available. Turn on location sharing in your profile to appear in donor searches.');
    }

    public function test_the_birthdate_cannot_move_after_a_recorded_donation(): void
    {
        $donor = $this->verifiedDonor(['birthdate' => '1995-01-01']);
        $donor->donations()->create(['donated_on' => '2015-06-01', 'is_self_reported' => true]);

        $this->actingAs($donor->user)->put(route('profile.update'), [
            'first_name' => $donor->user->first_name,
            'last_name' => $donor->user->last_name,
            'mobile_number' => $donor->user->mobile_number,
            'blood_type' => $donor->blood_type,
            'birthdate' => '2016-01-01',
            'weight_kg' => 60,
            'city' => 'Butuan City',
            'location_sharing' => '1',
        ])->assertSessionHasErrors(['birthdate' => 'Your birthdate must be before your first recorded donation.']);

        $this->assertSame('1995-01-01', $donor->refresh()->birthdate->toDateString());
    }

    // --- Pages ---

    public function test_emergency_and_find_more_forms_send_only_once(): void
    {
        $requester = User::factory()->create();

        $this->actingAs($requester)->get(route('requests.create'))
            ->assertSee('data-submit-once', false)
            ->assertSee('js/lifeline.js', false);
    }
}
