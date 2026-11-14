<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Who may open which page: one row per role and protected route. */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function accessMatrix(): array
    {
        return [
            'donor opens home' => ['donor', '/home', 200],
            'requester opens home' => ['requester', '/home', 200],
            'admin home goes to admin area' => ['admin', '/home', 302],
            'donor blocked from admin area' => ['donor', '/admin/donors/pending', 403],
            'requester blocked from admin area' => ['requester', '/admin/donors/pending', 403],
            'admin opens admin area' => ['admin', '/admin/donors/pending', 200],
            'donor opens profile' => ['donor', '/profile', 200],
            'requester opens profile' => ['requester', '/profile', 200],
            'admin has no profile page' => ['admin', '/profile', 403],
            'requester has no donation history' => ['requester', '/donations', 403],
            'admin has no donation history' => ['admin', '/donations', 403],
        ];
    }

    #[DataProvider('accessMatrix')]
    public function test_role_access(string $role, string $url, int $status): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->get($url)
            ->assertStatus($status);
    }

    public static function protectedUrls(): array
    {
        return [
            'home' => ['/home'],
            'admin area' => ['/admin/donors/pending'],
            'profile' => ['/profile'],
            'donation history' => ['/donations'],
        ];
    }

    #[DataProvider('protectedUrls')]
    public function test_guests_are_sent_to_login(string $url): void
    {
        $this->get($url)->assertRedirect(route('login'));
    }

    #[DataProvider('protectedUrls')]
    public function test_unverified_emails_are_sent_to_the_verification_notice(string $url): void
    {
        $user = User::factory()->unverified()->create(['role' => 'admin']);

        $this->actingAs($user)->get($url)->assertRedirect(route('verification.notice'));
    }
}
