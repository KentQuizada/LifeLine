<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BottomNavTest extends TestCase
{
    use RefreshDatabase;

    public static function appRoles(): array
    {
        return ['donor' => ['donor'], 'requester' => ['requester']];
    }

    #[DataProvider('appRoles')]
    public function test_donors_and_requesters_get_home_and_profile_tabs(string $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->get('/home')
            ->assertOk()
            ->assertSee('aria-label="Main"', false)
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('href="'.route('profile.show').'"', false);
    }

    public function test_profile_tab_is_marked_current_on_the_profile_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->get('/profile')
            ->assertOk()
            ->assertSeeInOrder(['href="'.route('profile.show').'"', 'aria-current="page"'], false);
    }

    public function test_admin_pages_have_no_bottom_nav(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/donors/pending')
            ->assertOk()
            ->assertDontSee('aria-label="Main"', false);
    }
}
