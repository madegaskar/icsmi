<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreadcrumbTest extends TestCase
{
    use RefreshDatabase;

    public function test_inner_page_shows_home_then_its_own_name_below_the_header(): void
    {
        $this->get('/submission')
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('href="'.url('/').'"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_home_page_shows_no_breadcrumb(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('aria-label="Breadcrumb"', false);
    }

    public function test_breadcrumb_uses_the_page_heading_as_the_current_label(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('aria-current="page" class="text-gray-900">Log in</span>', false);
    }
}
