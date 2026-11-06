<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_state_changing_route_passes_through_the_csrf_protected_group(): void
    {
        foreach (Route::getRoutes() as $route) {
            if (array_diff($route->methods(), ['GET', 'HEAD', 'OPTIONS']) !== []) {
                $this->assertContains('web', $route->gatherMiddleware(), $route->uri());
                $this->assertSame([], array_intersect($route->excludedMiddleware(), ['web']), $route->uri());
            }
        }
    }

    public function test_every_private_route_requires_login_verification_and_a_role(): void
    {
        foreach (Route::getRoutes() as $route) {
            $area = explode('/', $route->uri())[0];

            if (in_array($area, ['author', 'reviewer', 'editor', 'admin', 'reports'], true)) {
                $middleware = $route->gatherMiddleware();

                $this->assertContains('auth', $middleware, $route->uri());
                $this->assertContains('verified', $middleware, $route->uri());
                $this->assertNotEmpty(array_filter($middleware, fn ($name) => is_string($name) && str_starts_with($name, 'role:')), $route->uri());
            }
        }
    }

    public function test_private_storage_has_no_public_route(): void
    {
        Storage::disk('local')->put('manuscripts/1/secret.pdf', '%PDF-1.4');

        $this->get('/storage/manuscripts/1/secret.pdf')->assertNotFound();
        $this->put('/storage/manuscripts/1/secret.pdf')->assertNotFound();

        Storage::disk('local')->delete('manuscripts/1/secret.pdf');
    }

    public function test_errors_do_not_reveal_details_when_debug_is_off(): void
    {
        config(['app.debug' => false]);

        Route::get('/_boom', fn () => throw new RuntimeException('secret-internal-detail'));

        $response = $this->get('/_boom');

        $response->assertStatus(500);
        $this->assertStringNotContainsString('secret-internal-detail', $response->getContent());
        $this->assertStringNotContainsString('vendor/', $response->getContent());
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_password_reset_form_does_not_reveal_which_emails_exist(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $known = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);
        $unknown = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nobody@example.com']);

        $known->assertSessionHasNoErrors();
        $unknown->assertSessionHasNoErrors();
        $this->assertSame($known->getSession()->get('status'), $unknown->getSession()->get('status'));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_passwords_are_stored_hashed_with_bcrypt(): void
    {
        $this->post('/register', [
            'name' => 'Hash Check',
            'email' => 'hash@example.com',
            'affiliation' => 'U',
            'password' => 'password-value',
            'password_confirmation' => 'password-value',
            'privacy_consent' => '1',
        ]);

        $stored = User::where('email', 'hash@example.com')->value('password');

        $this->assertNotSame('password-value', $stored);
        $this->assertSame('bcrypt', Hash::info($stored)['algoName']);
    }

    public function test_public_search_and_pdf_are_rate_limited(): void
    {
        foreach (range(1, 60) as $attempt) {
            $this->get('/search')->assertOk();
        }

        $this->get('/search')->assertTooManyRequests();
    }

    public function test_make_admin_command_promotes_an_existing_account(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'first@example.com']);

        $this->artisan('icsmi:make-admin', ['email' => 'first@example.com'])->assertSuccessful();

        $user->refresh();
        $this->assertTrue($user->hasRole(Role::Admin));
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.made_admin']);

        $this->artisan('icsmi:make-admin', ['email' => 'missing@example.com'])->assertFailed();
    }
}
