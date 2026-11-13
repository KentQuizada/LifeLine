<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_onboarding_with_register_and_login_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('LifeLine')
            ->assertSee(route('register.step1'))
            ->assertSee(route('login'));
    }

    public function test_logged_in_user_skips_onboarding(): void
    {
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('home'));
    }
}
