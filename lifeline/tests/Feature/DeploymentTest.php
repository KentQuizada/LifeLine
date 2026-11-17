<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Online hosting (docs/deployment.md): proxy headers, demo password and first-start seeding. */
class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_links_use_https_behind_the_hosts_proxy(): void
    {
        $this->get('/login', ['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'lifeline.example.com'])
            ->assertOk()
            ->assertSee('https://lifeline.example.com/login', false);
    }

    public function test_the_seeder_uses_the_private_online_password_when_set(): void
    {
        config(['lifeline.seed_password' => 'Secret-Demo-2026']);

        $this->seed();

        $admin = User::where('email', 'admin@lifeline.test')->firstOrFail();
        $this->assertTrue(Hash::check('Secret-Demo-2026', $admin->password));
        $this->assertFalse(Hash::check('password', $admin->password));
    }

    public function test_first_start_seeds_an_empty_database_and_later_starts_do_not(): void
    {
        Artisan::call('lifeline:seed-if-empty');
        $users = User::count();
        $this->assertGreaterThan(0, $users);

        Artisan::call('lifeline:seed-if-empty');
        $this->assertSame($users, User::count());
        $this->assertStringContainsString('already has users', Artisan::output());
    }

    public function test_the_production_settings_example_is_safe(): void
    {
        $example = file_get_contents(base_path('.env.production.example'));

        $this->assertStringContainsString('APP_DEBUG=false', $example);
        $this->assertStringContainsString('APP_ENV=production', $example);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $example);
        $this->assertMatchesRegularExpression('/^APP_KEY=$/m', $example);
    }
}
