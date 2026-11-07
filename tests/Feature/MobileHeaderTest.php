<?php

namespace Tests\Feature;

use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_menu_puts_login_and_register_in_a_right_aligned_button_row(): void
    {
        $this->seed(MenuSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-mobile-auth-actions', false)
            ->assertSee('justify-end', false)
            ->assertSee('data-mobile-toggle', false);
    }

    public function test_login_and_register_are_not_listed_as_plain_links_in_the_mobile_menu(): void
    {
        $this->seed(MenuSeeder::class);

        $html = $this->get('/')->assertOk()->getContent();
        $mobile = substr($html, (int) strpos($html, 'data-mobile-toggle'));
        $mobile = substr($mobile, 0, (int) strpos($mobile, '</details>'));

        $this->assertStringNotContainsString('block text-gray-700" href="http://localhost/login"', $mobile);
        $this->assertStringContainsString('data-mobile-auth-actions', $mobile);
    }
}
