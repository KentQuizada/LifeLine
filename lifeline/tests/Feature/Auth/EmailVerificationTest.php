<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function verificationUrl(User $user, ?string $hash = null): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => $hash ?? sha1($user->getEmailForVerification()),
        ]);
    }

    public function test_unverified_user_is_blocked_from_the_app(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/home')->assertRedirect(route('verification.notice'));
    }

    public function test_verification_notice_is_shown_to_unverified_user(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'juan@example.test']);

        $this->actingAs($user)->get('/email/verify')->assertOk()->assertSee('juan@example.test');
    }

    public function test_verified_user_skips_the_notice(): void
    {
        $this->actingAs(User::factory()->create())->get('/email/verify')->assertRedirect(route('home'));
    }

    public function test_valid_link_verifies_the_email(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get($this->verificationUrl($user))->assertRedirect(route('home'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_link_with_wrong_hash_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get($this->verificationUrl($user, sha1('someone-else@example.test')))
            ->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_link_for_another_user_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $other = User::factory()->unverified()->create();

        $this->actingAs($user)->get($this->verificationUrl($other))->assertForbidden();

        $this->assertFalse($other->fresh()->hasVerifiedEmail());
    }

    public function test_unsigned_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/email/verify/'.$user->id.'/'.sha1($user->email))
            ->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_email_can_be_resent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post('/email/verification-notification')->assertSessionHas('status');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_can_correct_a_mistyped_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['email' => 'juan@gmail.con']);

        $this->actingAs($user)
            ->from('/email/verify')
            ->put('/email/verify/address', ['email' => ' Juan@Gmail.com '])
            ->assertRedirect('/email/verify')
            ->assertSessionHas('status');

        $this->assertSame('juan@gmail.com', $user->fresh()->email);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_corrected_email_must_be_free_and_not_a_typo(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'taken@example.test']);
        $user = User::factory()->unverified()->create(['email' => 'juan@gmail.con']);

        $this->actingAs($user)->put('/email/verify/address', ['email' => 'taken@example.test'])
            ->assertSessionHasErrors('email');
        $this->actingAs($user)->put('/email/verify/address', ['email' => 'juan@gmial.com'])
            ->assertSessionHasErrors(['email' => 'Check your email address. Did you mean juan@gmail.com?']);

        $this->assertSame('juan@gmail.con', $user->fresh()->email);
        Notification::assertNothingSent();
    }

    public function test_verified_user_cannot_change_the_email_here(): void
    {
        $user = User::factory()->create(['email' => 'juan@example.test']);

        $this->actingAs($user)->put('/email/verify/address', ['email' => 'other@example.test'])
            ->assertRedirect(route('home'));

        $this->assertSame('juan@example.test', $user->fresh()->email);
    }

    public function test_verify_page_offers_the_spam_tip_and_the_change_form(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/email/verify')
            ->assertSee('Spam')
            ->assertSee('Wrong email address? Change it');
    }
}
