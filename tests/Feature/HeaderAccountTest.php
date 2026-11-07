<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_register_and_login_as_buttons_on_desktop(): void
    {
        $this->seed(MenuSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-desktop-auth-actions', false)
            ->assertSee('bg-white px-4 py-2 font-semibold text-emerald-950', false)
            ->assertSee('border-white/70 px-4 py-2 font-semibold text-white', false);
    }

    public function test_signed_in_user_sees_name_menu_bell_and_logout_instead_of_login(): void
    {
        $this->seed(MenuSeeder::class);
        $user = User::factory()->create(['name' => 'Siti Penulis']);
        $user->assignRole(Role::Author);

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('Siti Penulis')
            ->assertSee('data-account-menu', false)
            ->assertSee('data-unread-count', false)
            ->assertSee('Log out')
            ->assertDontSee('data-desktop-auth-actions', false);
    }

    public function test_account_menu_links_to_dashboard_profile_and_notifications(): void
    {
        $this->seed(MenuSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Role::Author);

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertSee('href="'.route('profile.edit').'"', false)
            ->assertSee('href="'.route('notifications.index').'"', false);
    }

    public function test_signed_in_user_is_not_offered_login_or_register_in_the_header(): void
    {
        $this->seed(MenuSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Role::Author);

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();
        $header = substr($html, (int) strpos($html, '<header'), (int) strpos($html, '</header>') - (int) strpos($html, '<header'));

        $this->assertStringNotContainsString('>Login<', $header);
        $this->assertStringNotContainsString('>Register<', $header);
    }
}
