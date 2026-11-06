<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SecurityFoundationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string}>
     */
    public static function stateChangingRoutes(): array
    {
        return [
            'login' => ['post', '/login'],
            'register' => ['post', '/register'],
            'logout' => ['post', '/logout'],
            'profile update' => ['patch', '/profile'],
            'password update' => ['put', '/password'],
        ];
    }

    #[DataProvider('stateChangingRoutes')]
    public function test_requests_without_a_csrf_token_are_rejected(string $method, string $uri): void
    {
        $this->enforceCsrf();

        $this->actingAs(User::factory()->create());
        $this->withSession(['_token' => 'expected-token']);

        $this->{$method}($uri, ['name' => 'Changed'])->assertStatus(419);
    }

    public function test_requests_with_a_valid_csrf_token_are_accepted(): void
    {
        $this->enforceCsrf();

        $this->withSession(['_token' => 'expected-token'])
            ->post('/login', ['_token' => 'expected-token', 'email' => 'nobody@example.com', 'password' => 'wrong'])
            ->assertRedirect();
    }

    public function test_csrf_rejection_leaves_data_unchanged(): void
    {
        $this->enforceCsrf();

        $user = User::factory()->create(['name' => 'Original']);

        $this->actingAs($user)->withSession(['_token' => 'expected-token'])
            ->patch('/profile', ['name' => 'Changed', 'email' => $user->email])
            ->assertStatus(419);

        $this->assertSame('Original', $user->fresh()->name);
    }

    public function test_user_input_is_rendered_as_plain_text(): void
    {
        $payload = '<script>alert("xss")</script>';

        $user = User::factory()->create(['name' => $payload, 'affiliation' => '"><img src=x onerror=alert(1)>']);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $this->assertStringNotContainsString($payload, $response->getContent());
        $this->assertStringNotContainsString('<img src=x', $response->getContent());
        $this->assertStringContainsString(e($payload), $response->getContent());
    }

    public function test_views_have_no_raw_output_or_inline_handlers(): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            $contents = file_get_contents($file->getPathname());

            $this->assertDoesNotMatchRegularExpression('/\{!!|<script(?![^>]*\bsrc=)|\son[a-z]+\s*=/i', $contents, $file->getPathname());
        }
    }

    public function test_sql_injection_payloads_do_not_authenticate_or_alter_data(): void
    {
        $user = User::factory()->create();
        $payload = "' OR '1'='1' -- ";
        $destructive = "'; DROP TABLE users; --";

        $this->post('/login', ['email' => $payload, 'password' => $payload])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email."' -- ", 'password' => 'anything'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $destructive, 'password' => $destructive])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_sql_injection_payload_is_stored_as_literal_text(): void
    {
        $user = User::factory()->create();
        $payload = "x'); DROP TABLE users; --";

        $this->actingAs($user)
            ->patch('/profile', ['name' => $user->name, 'email' => $user->email, 'affiliation' => $payload])
            ->assertSessionHasNoErrors();

        $this->assertSame($payload, $user->fresh()->affiliation);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_security_headers_are_sent(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $policy = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringNotContainsString('unsafe-inline', $policy);
        $this->assertStringNotContainsString('unsafe-eval', $policy);
    }

    public function test_error_pages_have_no_inline_styles(): void
    {
        $response = $this->get('/does-not-exist');

        $response->assertNotFound();
        $this->assertStringNotContainsString('<style', $response->getContent());
        $this->assertStringNotContainsString('style=', $response->getContent());
    }

    public function test_session_cookie_is_protected(): void
    {
        $cookie = collect($this->get('/login')->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));

        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    /**
     * @return array<string, array{Role}>
     */
    public static function roles(): array
    {
        return collect(Role::cases())->mapWithKeys(fn (Role $role) => [$role->value => [$role]])->all();
    }

    #[DataProvider('roles')]
    public function test_each_workspace_only_admits_its_own_role(Role $role): void
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        foreach (Role::cases() as $workspace) {
            $response = $this->actingAs($user)->get('/'.$workspace->value);

            $workspace === $role ? $response->assertOk() : $response->assertForbidden();
        }
    }

    public function test_guests_are_redirected_from_workspaces(): void
    {
        foreach (Role::cases() as $workspace) {
            $this->get('/'.$workspace->value)->assertRedirect('/login');
        }
    }

    public function test_unverified_users_cannot_open_workspaces(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole(Role::Author);

        $this->actingAs($user)->get('/author')->assertRedirect(route('verification.notice'));
    }

    public function test_registration_assigns_only_the_author_role(): void
    {
        $this->post('/register', [
            'name' => 'New Author',
            'email' => 'author@example.com',
            'affiliation' => 'Example University',
            'password' => 'password',
            'password_confirmation' => 'password',
            'privacy_consent' => '1',
            'role' => 'admin',
            'roles' => ['admin'],
            'email_verified_at' => now()->toDateTimeString(),
        ])->assertRedirect();

        $user = User::where('email', 'author@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole(Role::Author));
        $this->assertFalse($user->hasRole(Role::Admin, Role::Editor, Role::Reviewer));
        $this->assertNull($user->email_verified_at);
    }

    public function test_invalid_orcid_is_rejected(): void
    {
        $this->post('/register', [
            'name' => 'New Author',
            'email' => 'author@example.com',
            'affiliation' => 'Example University',
            'orcid' => 'javascript:alert(1)',
            'password' => 'password',
            'password_confirmation' => 'password',
            'privacy_consent' => '1',
        ])->assertSessionHasErrors('orcid');
    }

    public function test_repeated_login_failures_are_throttled_and_audited(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(5, AuditLog::where('action', 'auth.failed')->count());
        $this->assertSame(1, AuditLog::where('action', 'auth.lockout')->count());
    }

    public function test_successful_login_is_audited_without_the_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login', 'user_id' => $user->id]);
        $this->assertStringNotContainsString('password', (string) json_encode(AuditLog::all()->toArray()));
    }

    public function test_sqlite_enforces_foreign_keys(): void
    {
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
    }

    public function test_sqlite_is_configured_for_wal(): void
    {
        $this->assertSame('wal', config('database.connections.sqlite.journal_mode'));
    }

    private function enforceCsrf(): void
    {
        // The CSRF middleware skips itself in the "testing" environment.
        $this->app['env'] = 'local';
    }
}
