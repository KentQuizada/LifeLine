<?php

namespace Tests\Feature\Admin;

use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\DonorMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** M2.6: the admin dashboard, statistics API, records and role protection. */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public static function adminPages(): array
    {
        return [
            'dashboard' => ['admin.dashboard'],
            'donors' => ['admin.donors.index'],
            'users' => ['admin.users.index'],
            'requests' => ['admin.requests.index'],
            'hospitals' => ['admin.hospitals.index'],
            'new hospital' => ['admin.hospitals.create'],
            'reports' => ['admin.reports.index'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_load_for_admins_only(string $route): void
    {
        $this->actingAs($this->admin)->get(route($route))->assertOk()->assertSee('aria-label="Admin"', false);

        foreach (['donor', 'requester'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get(route($route))->assertForbidden();
        }
        auth()->logout();
        $this->get(route($route))->assertRedirect(route('login'));
    }

    public function test_every_admin_route_requires_the_admin_role(): void
    {
        $adminRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'admin.') || $route->getName() === 'api.admin.stats');

        $this->assertGreaterThan(15, $adminRoutes->count());
        $adminRoutes->each(fn ($route) => $this->assertContains('role:admin', $route->gatherMiddleware(), $route->getName()));
    }

    public function test_admins_land_on_the_dashboard_after_login(): void
    {
        $this->actingAs($this->admin)->get(route('home'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_statistics_match_database_counts(): void
    {
        Donor::factory()->count(3)->create(['verification_status' => 'verified', 'blood_type' => 'O+', 'is_available' => true, 'location_sharing' => true]);
        Donor::factory()->count(2)->create(['verification_status' => 'pending', 'blood_type' => 'A-']);
        BloodRequest::factory()->count(4)->create(['status' => 'open']);
        BloodRequest::factory()->create(['status' => 'fulfilled']);

        $response = $this->actingAs($this->admin)->getJson(route('api.admin.stats'))->assertOk();

        $response->assertJsonPath('data.users_by_role.donor', User::where('role', 'donor')->count())
            ->assertJsonPath('data.users_by_role.admin', 1)
            ->assertJsonPath('data.donors_by_status.verified', 3)
            ->assertJsonPath('data.donors_by_status.pending', 2)
            ->assertJsonPath('data.donors_by_blood_type.O+', 3)
            ->assertJsonPath('data.donors_by_blood_type.B+', 0)
            ->assertJsonPath('data.searchable_donors', Donor::searchable()->count())
            ->assertJsonPath('data.requests_by_status.open', 4)
            ->assertJsonPath('data.requests_by_status.fulfilled', 1)
            ->assertJsonPath('data.total_requests', BloodRequest::count())
            ->assertJsonPath('data.fulfilled_percent', 20)
            ->assertJsonCount(14, 'data.requests_last_14_days')
            ->assertJsonPath('data.requests_last_14_days.13.count', 5);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertSee('vendor/chartjs/chart.umd.js', false)
            ->assertSee(route('api.admin.stats'), false);
    }

    public function test_the_stats_api_is_admin_only_and_json(): void
    {
        $this->getJson(route('api.admin.stats'))->assertUnauthorized();
        $this->actingAs(User::factory()->create(['role' => 'donor']))->getJson(route('api.admin.stats'))->assertForbidden();
    }

    public function test_donor_list_filters_and_record_page(): void
    {
        $ana = Donor::factory()->for(User::factory()->state(['role' => 'donor', 'first_name' => 'Ana', 'last_name' => 'Reyes']))
            ->create(['blood_type' => 'AB-', 'verification_status' => 'verified']);
        Donor::factory()->for(User::factory()->state(['role' => 'donor', 'first_name' => 'Ben', 'last_name' => 'Cruz']))
            ->create(['blood_type' => 'O+', 'verification_status' => 'pending']);

        $this->actingAs($this->admin)->get(route('admin.donors.index', ['blood_type' => 'AB-']))
            ->assertSee('Ana Reyes')->assertDontSee('Ben Cruz');
        $this->actingAs($this->admin)->get(route('admin.donors.index', ['q' => 'Cruz']))
            ->assertSee('Ben Cruz')->assertDontSee('Ana Reyes');
        $this->actingAs($this->admin)->get(route('admin.donors.index', ['status' => 'bogus']))->assertSessionHasErrors('status');

        $this->actingAs($this->admin)->get(route('admin.donors.show', $ana))->assertOk()->assertSee('Ana Reyes')->assertSee('AB-');
    }

    public function test_user_list_filters_by_role(): void
    {
        User::factory()->create(['role' => 'requester', 'first_name' => 'Rita', 'last_name' => 'Requester']);

        $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'requester']))
            ->assertSee('Rita Requester');
        $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'donor']))
            ->assertDontSee('Rita Requester');
    }

    public function test_request_list_search_and_detail(): void
    {
        $bloodRequest = BloodRequest::factory()->create(['patient_name' => 'Pedro Penduko']);
        $donor = Donor::factory()->create();
        DonorMatch::create(['blood_request_id' => $bloodRequest->id, 'donor_id' => $donor->id, 'distance_km' => 2.4]);

        $this->actingAs($this->admin)->get(route('admin.requests.index', ['q' => strtolower($bloodRequest->reference_number)]))
            ->assertSee($bloodRequest->reference_number);
        $this->actingAs($this->admin)->get(route('admin.requests.show', $bloodRequest))
            ->assertOk()->assertSee('Pedro Penduko')->assertSee('Potential donors (1)');
    }
}
