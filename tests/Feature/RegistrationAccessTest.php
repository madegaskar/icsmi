<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::Admin);

        return $admin;
    }

    public function test_registration_is_open_by_default(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_closed_registration_sends_visitors_to_login_with_a_notice(): void
    {
        SiteSetting::put(SiteSetting::REGISTRATION_OPEN, '0');

        $this->get('/register')
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
    }

    public function test_closed_registration_does_not_create_accounts(): void
    {
        SiteSetting::put(SiteSetting::REGISTRATION_OPEN, '0');

        $this->post('/register', [
            'name' => 'Calon Akun',
            'email' => 'calon@example.test',
            'password' => 'password-kuat-123',
            'password_confirmation' => 'password-kuat-123',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', ['email' => 'calon@example.test']);
    }

    public function test_login_stays_available_when_registration_is_closed(): void
    {
        SiteSetting::put(SiteSetting::REGISTRATION_OPEN, '0');

        $this->get('/login')->assertOk();
    }

    public function test_admin_can_close_and_reopen_registration(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.settings.update'), ['registration_open' => '0'])
            ->assertRedirect(route('admin.settings.edit'));
        $this->assertFalse(SiteSetting::registrationOpen());

        $this->actingAs($admin)->put(route('admin.settings.update'), ['registration_open' => '1']);
        $this->assertTrue(SiteSetting::registrationOpen());
    }

    public function test_admin_settings_page_shows_the_current_registration_state(): void
    {
        SiteSetting::put(SiteSetting::REGISTRATION_OPEN, '0');

        $this->actingAs($this->admin())->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('New account registration')
            ->assertSee('value="0" class="text-emerald-800 focus:ring-emerald-700" checked', false);
    }

    public function test_only_admins_can_change_registration(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::Editor);

        $this->actingAs($editor)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.settings.update'), ['registration_open' => '0'])->assertForbidden();
    }

    public function test_header_hides_register_while_registration_is_closed(): void
    {
        $this->seed(MenuSeeder::class);
        SiteSetting::put(SiteSetting::REGISTRATION_OPEN, '0');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="/register"', false)
            ->assertSee('href="/login"', false);
    }
}
