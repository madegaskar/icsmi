<?php

namespace Tests\Feature;

use App\Models\PageVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VisitorStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['trustedproxy.proxies' => '10.0.0.1']);
    }

    public function test_public_visit_is_counted_under_the_country_from_the_proxy_header(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->withHeaders(['CF-IPCountry' => 'ID'])
            ->get('/')
            ->assertOk();

        $this->assertDatabaseHas('page_visits', ['country_code' => 'ID', 'count' => 1]);
    }

    public function test_visits_without_a_country_header_are_counted_as_unknown(): void
    {
        $this->get('/')->assertOk();

        $this->assertDatabaseHas('page_visits', ['country_code' => '', 'count' => 1]);
    }

    public function test_repeat_visits_from_one_country_add_to_the_same_daily_count(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->withHeaders(['CF-IPCountry' => 'MY'])->get('/');
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->withHeaders(['CF-IPCountry' => 'MY'])->get('/');

        $this->assertSame(1, PageVisit::count());
        $this->assertDatabaseHas('page_visits', ['country_code' => 'MY', 'count' => 2]);
    }

    public function test_bots_and_staff_pages_are_not_counted(): void
    {
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)'])->get('/');
        $this->get('/login');
        $this->get('/library');

        $this->assertSame(0, PageVisit::count());
    }

    public function test_no_ip_address_is_stored(): void
    {
        $this->assertFalse(Schema::hasColumn('page_visits', 'ip_address'));
        $this->assertFalse(Schema::hasColumn('page_visits', 'ip'));
    }

    public function test_sidebar_shows_the_total_and_each_country_with_its_flag(): void
    {
        $this->seed(\Database\Seeders\SidebarSectionSeeder::class);
        PageVisit::create(['visited_on' => now()->toDateString(), 'country_code' => 'ID', 'count' => 3]);
        PageVisit::create(['visited_on' => now()->toDateString(), 'country_code' => 'US', 'count' => 1]);

        $this->get('/')
            ->assertOk()
            ->assertSee('VISITOR')
            ->assertSee('Total visits: 4')
            ->assertSee('🇮🇩')
            ->assertSee('Indonesia')
            ->assertSee('🇺🇸');
    }

    public function test_sidebar_has_template_and_accreditation_sections(): void
    {
        $this->seed(\Database\Seeders\SidebarSectionSeeder::class);
        $this->get('/')
            ->assertOk()
            ->assertSee('TEMPLATE')
            ->assertSee('ACCREDITATION');
    }
}
