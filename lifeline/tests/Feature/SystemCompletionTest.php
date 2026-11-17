<?php

namespace Tests\Feature;

use App\Models\Donor;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Screens and pieces that complete the system: error pages, Home map, live badge, offline page, admin suspend. */
class SystemCompletionTest extends TestCase
{
    use RefreshDatabase;

    // --- Error pages ---

    public function test_unknown_pages_show_the_lifeline_404_page(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('Go to Home')
            ->assertSee('css/lifeline.css', false);
    }

    public function test_forbidden_pages_show_the_lifeline_403_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('Not allowed');
    }

    public function test_server_errors_show_a_friendly_page_without_details(): void
    {
        config(['app.debug' => false]);
        Route::get('/_test/boom', fn () => throw new \RuntimeException('secret detail'));

        $this->get('/_test/boom')
            ->assertStatus(500)
            ->assertSee('Something went wrong')
            ->assertSee('contact the hospital or blood bank directly')
            ->assertDontSee('secret detail');
    }

    public function test_api_errors_stay_json(): void
    {
        $this->getJson('/api/v1/does-not-exist')->assertNotFound()->assertJsonStructure(['message']);
    }

    // --- Home "Donors near you" ---

    public function test_home_shows_the_donors_near_you_preview(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))->get(route('home'))
            ->assertOk()
            ->assertSee('Donors near you')
            ->assertSee('data-center-lat="8.95"', false)
            ->assertSee('js/home-map.js', false);
    }

    public function test_the_preview_centres_on_the_donors_own_approximate_location(): void
    {
        $donor = Donor::factory()->create(['approx_latitude' => 8.93, 'approx_longitude' => 125.51, 'location_sharing' => true]);

        $this->actingAs($donor->user)->get(route('home'))
            ->assertSee('data-lat="8.93"', false)
            ->assertSee('data-lng="125.51"', false);
    }

    // --- Live unread badge ---

    public function test_the_unread_count_endpoint_returns_only_my_count(): void
    {
        $user = User::factory()->create(['role' => 'requester']);
        Notification::create(['user_id' => $user->id, 'title' => 'A', 'message' => 'A']);
        Notification::create(['user_id' => $user->id, 'title' => 'B', 'message' => 'B', 'read_at' => now()]);
        Notification::create(['user_id' => User::factory()->create()->id, 'title' => 'C', 'message' => 'C']);

        $this->actingAs($user)->getJson(route('api.notifications.count'))
            ->assertOk()
            ->assertExactJson(['unread_count' => 1]);
        $this->getJson(route('api.notifications.count'))->assertOk(); // still signed in above
    }

    public function test_guests_get_401_from_the_unread_count(): void
    {
        $this->getJson(route('api.notifications.count'))->assertUnauthorized();
    }

    public function test_the_badge_knows_where_to_poll(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'requester']))->get(route('home'))
            ->assertSee('data-poll="'.route('api.notifications.count').'"', false)
            ->assertSee('data-unread-text', false);
    }

    // --- Offline page and service worker ---

    public function test_the_offline_page_and_service_worker_exist(): void
    {
        $offline = file_get_contents(public_path('offline.html'));
        $worker = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString('You are offline', $offline);
        $this->assertStringContainsString("'offline.html'", $worker);
        // Only static files are stored; pages and the API are never cached (personal data).
        $this->assertStringContainsString('(css|js|vendor|icons)', $worker);
        $this->assertSame(1, substr_count($worker, 'cache.put('));
        $this->assertStringContainsString('serviceWorker', file_get_contents(public_path('js/lifeline.js')));
    }

    // --- Admin: suspend and reinstate ---

    public function test_an_admin_can_suspend_and_reinstate_a_verified_donor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $donor = Donor::factory()->create(['verification_status' => 'verified']);

        $this->actingAs($admin)->get(route('admin.donors.show', $donor))->assertSee('Suspend')->assertDontSee('>Reject<', false);

        $this->actingAs($admin)->post(route('admin.donors.suspend', $donor))->assertSessionHas('status');
        $this->assertSame('rejected', $donor->refresh()->verification_status);
        $this->assertDatabaseHas('notifications', ['user_id' => $donor->user_id, 'title' => 'Your donor account was suspended']);

        $this->actingAs($admin)->post(route('admin.donors.reinstate', $donor))->assertSessionHas('status');
        $this->assertSame('verified', $donor->refresh()->verification_status);
        $this->assertSame($admin->id, $donor->verified_by);
    }

    public function test_suspend_and_reinstate_only_work_from_the_right_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pending = Donor::factory()->create(['verification_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.donors.suspend', $pending))->assertSessionHas('error');
        $this->actingAs($admin)->post(route('admin.donors.reinstate', $pending))->assertSessionHas('error');
        $this->assertSame('pending', $pending->refresh()->verification_status);
    }

    public function test_only_admins_can_suspend(): void
    {
        $donor = Donor::factory()->create(['verification_status' => 'verified']);

        $this->actingAs(User::factory()->create(['role' => 'requester']))
            ->post(route('admin.donors.suspend', $donor))
            ->assertForbidden();
    }
}
