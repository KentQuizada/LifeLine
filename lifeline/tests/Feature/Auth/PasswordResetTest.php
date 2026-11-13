<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const SENT_MESSAGE = 'If an account uses that email address, we sent it a password reset link.';

    public function test_forgot_password_page_renders(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('Reset your password');
    }

    public function test_reset_link_is_sent_to_an_existing_account(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'juan@example.test']);

        $this->post('/forgot-password', ['email' => 'juan@example.test'])
            ->assertSessionHas('status', self::SENT_MESSAGE);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_message(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'nobody@example.test'])
            ->assertSessionHas('status', self::SENT_MESSAGE);

        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'juan@example.test']);

        $this->post('/forgot-password', ['email' => 'juan@example.test']);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->get('/reset-password/'.$notification->token.'?email='.urlencode($user->email))->assertOk();

            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])->assertRedirect(route('login'));

            return true;
        });

        $this->assertTrue(Hash::check('newpass123', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post('/reset-password', [
            'token' => 'not-a-real-token',
            'email' => $user->email,
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_new_password_must_be_strong(): void
    {
        $user = User::factory()->create();

        $this->post('/reset-password', [
            'token' => 'any',
            'email' => $user->email,
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors('password');
    }

    public function test_reset_password_post_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/reset-password', [])->assertStatus(302);
        }

        $this->post('/reset-password', [])->assertStatus(429);
    }

    public function test_reset_form_ignores_array_email_query(): void
    {
        $this->get('/reset-password/sometoken?email[]=x')->assertOk();
    }
}
