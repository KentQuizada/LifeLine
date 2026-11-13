<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Email or mobile number');
    }

    public function test_user_can_log_in_with_email(): void
    {
        $user = User::factory()->create(['email' => 'juan@example.test']);

        $this->post('/login', ['login' => 'juan@example.test', 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_email_login_ignores_letter_case(): void
    {
        $user = User::factory()->create(['email' => 'juan@example.test']);

        $this->post('/login', ['login' => 'JUAN@Example.test', 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_log_in_with_mobile_number_in_any_format(): void
    {
        $user = User::factory()->create(['mobile_number' => '09171234567']);

        $this->post('/login', ['login' => '+63 917 123 4567', 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'juan@example.test']);

        $this->from('/login')
            ->post('/login', ['login' => 'juan@example.test', 'password' => 'wrong-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login' => 'These credentials do not match our records.']);

        $this->assertGuest();
    }

    public function test_unknown_account_gets_the_same_error_as_a_wrong_password(): void
    {
        $this->post('/login', ['login' => 'nobody@example.test', 'password' => 'password'])
            ->assertSessionHasErrors(['login' => 'These credentials do not match our records.']);

        $this->assertGuest();
    }

    public function test_login_requires_both_fields(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['login', 'password']);
    }

    public function test_login_is_locked_after_five_failed_attempts(): void
    {
        User::factory()->create(['email' => 'juan@example.test']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['login' => 'juan@example.test', 'password' => 'wrong-password']);
        }

        // Even the correct password is refused while the account is locked.
        $this->post('/login', ['login' => 'juan@example.test', 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('login'));
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_logged_in_user_is_redirected_away_from_login_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect(route('home'));
    }

    public function test_guest_is_redirected_to_login_from_home(): void
    {
        $this->get('/home')->assertRedirect(route('login'));
    }

    public function test_logged_in_user_sees_home(): void
    {
        $user = User::factory()->create(['first_name' => 'Maria']);

        $this->actingAs($user)->get('/home')->assertOk()->assertSee('Hello, Maria!');
    }
}
