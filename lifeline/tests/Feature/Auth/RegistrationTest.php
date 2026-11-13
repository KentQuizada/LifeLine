<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function step1(array $overrides = []): array
    {
        return array_merge([
            'role' => 'donor',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria@example.test',
            'mobile_number' => '09171234567',
        ], $overrides);
    }

    private function step2(array $overrides = []): array
    {
        return array_merge([
            'blood_type' => 'O+',
            'birthdate' => '1998-05-20',
            'weight_kg' => '58.5',
            'city' => 'Butuan City',
        ], $overrides);
    }

    private function step3(array $overrides = []): array
    {
        return array_merge([
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'privacy_consent' => '1',
            'terms' => '1',
        ], $overrides);
    }

    public function test_registration_pages_render(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create your account');

        $this->post('/register', $this->step1());
        $this->get('/register/medical')->assertOk()->assertSee('Blood type');

        $this->post('/register/medical', $this->step2());
        $this->get('/register/account')->assertOk()->assertSee('Data Privacy Act');
    }

    public function test_donor_registers_in_three_steps(): void
    {
        Notification::fake();

        $this->post('/register', $this->step1())->assertRedirect(route('register.step2'));
        $this->post('/register/medical', $this->step2())->assertRedirect(route('register.step3'));
        $this->post('/register/account', $this->step3(['location_sharing' => '1']))
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'maria@example.test')->firstOrFail();
        $this->assertSame('donor', $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertDatabaseHas('donors', [
            'user_id' => $user->id,
            'blood_type' => 'O+',
            'city' => 'Butuan City',
            'location_sharing' => true,
            'verification_status' => 'pending',
        ]);
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_requester_skips_the_medical_step(): void
    {
        Notification::fake();

        $this->post('/register', $this->step1(['role' => 'requester']))->assertRedirect(route('register.step3'));
        $this->post('/register/account', $this->step3())->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'maria@example.test')->firstOrFail();
        $this->assertSame('requester', $user->role);
        $this->assertDatabaseMissing('donors', ['user_id' => $user->id]);
    }

    public function test_requester_cannot_open_the_medical_step(): void
    {
        $this->post('/register', $this->step1(['role' => 'requester']));

        $this->get('/register/medical')->assertRedirect(route('register.step1'));
    }

    public function test_donor_cannot_skip_the_medical_step(): void
    {
        $this->post('/register', $this->step1());

        $this->get('/register/account')->assertRedirect(route('register.step2'));
    }

    public function test_last_step_without_earlier_steps_redirects_to_step_one(): void
    {
        $this->get('/register/account')->assertRedirect(route('register.step1'));
        $this->post('/register/account', $this->step3())->assertRedirect(route('register.step1'));

        $this->assertDatabaseCount('users', 0);
    }

    public function test_nobody_can_register_as_admin(): void
    {
        $this->post('/register', $this->step1(['role' => 'admin']))->assertSessionHasErrors('role');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_role_sent_with_the_last_step_is_ignored(): void
    {
        Notification::fake();

        $this->post('/register', $this->step1(['role' => 'requester']));
        $this->post('/register/account', $this->step3(['role' => 'admin']));

        $this->assertSame('requester', User::where('email', 'maria@example.test')->value('role'));
    }

    public function test_duplicate_email_is_rejected_regardless_of_case(): void
    {
        User::factory()->create(['email' => 'maria@example.test']);

        $this->post('/register', $this->step1(['email' => 'MARIA@example.test']))->assertSessionHasErrors('email');
    }

    public function test_duplicate_mobile_number_in_another_format_is_rejected(): void
    {
        User::factory()->create(['mobile_number' => '09171234567']);

        $this->post('/register', $this->step1(['mobile_number' => '+63 917 123 4567']))
            ->assertSessionHasErrors('mobile_number');
    }

    public function test_invalid_mobile_number_is_rejected(): void
    {
        $this->post('/register', $this->step1(['mobile_number' => '12345']))->assertSessionHasErrors('mobile_number');
    }

    public function test_mobile_number_is_saved_in_local_format(): void
    {
        Notification::fake();

        $this->post('/register', $this->step1(['role' => 'requester', 'mobile_number' => '+639171234567']));
        $this->post('/register/account', $this->step3());

        $this->assertDatabaseHas('users', ['email' => 'maria@example.test', 'mobile_number' => '09171234567']);
    }

    public function test_email_taken_between_steps_sends_user_back_to_step_one(): void
    {
        $this->post('/register', $this->step1(['role' => 'requester']));
        User::factory()->create(['email' => 'maria@example.test']);

        $this->post('/register/account', $this->step3())
            ->assertRedirect(route('register.step1'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_medical_info_is_validated(): void
    {
        $this->post('/register', $this->step1());

        $this->post('/register/medical', $this->step2([
            'blood_type' => 'C+',
            'birthdate' => now()->addDay()->toDateString(),
            'weight_kg' => '0',
            'city' => '',
        ]))->assertSessionHasErrors(['blood_type', 'birthdate', 'weight_kg', 'city']);
    }

    public function test_password_must_be_confirmed_and_strong(): void
    {
        $this->post('/register', $this->step1(['role' => 'requester']));

        $this->post('/register/account', $this->step3(['password_confirmation' => 'different1']))
            ->assertSessionHasErrors('password');
        $this->post('/register/account', $this->step3(['password' => 'short1', 'password_confirmation' => 'short1']))
            ->assertSessionHasErrors('password');
        $this->post('/register/account', $this->step3(['password' => 'lettersonly', 'password_confirmation' => 'lettersonly']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_consent_and_terms_are_required(): void
    {
        $this->post('/register', $this->step1(['role' => 'requester']));

        $this->post('/register/account', $this->step3(['privacy_consent' => null, 'terms' => null]))
            ->assertSessionHasErrors(['privacy_consent', 'terms']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_posts_are_rate_limited(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->post('/register', $this->step1())->assertStatus(302);
        }

        $this->post('/register', $this->step1())->assertStatus(429);
    }

    public function test_array_input_is_rejected_without_a_server_error(): void
    {
        $this->post('/register', $this->step1(['email' => ['x'], 'mobile_number' => ['x']]))
            ->assertStatus(302)
            ->assertSessionHasErrors(['email', 'mobile_number']);
    }

    public function test_registration_records_when_consent_was_given(): void
    {
        Notification::fake();

        $this->post('/register', $this->step1(['role' => 'requester']))->assertRedirect(route('register.step3'));
        $this->post('/register/account', $this->step3())->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'maria@example.test')->firstOrFail();
        $this->assertNotNull($user->privacy_consent_at);
        $this->assertNotNull($user->terms_accepted_at);
    }
}
