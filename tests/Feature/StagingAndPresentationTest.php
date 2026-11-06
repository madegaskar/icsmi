<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StagingAndPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_blocks_every_crawler_when_site_noindex_is_on(): void
    {
        config(['site.noindex' => true]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /', false);
    }

    public function test_robots_txt_allows_crawlers_by_default(): void
    {
        config(['site.noindex' => false]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertDontSee('Disallow: /', false);
    }

    public function test_pages_send_noindex_header_when_site_noindex_is_on(): void
    {
        config(['site.noindex' => true]);

        $this->get('/')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_pages_do_not_send_noindex_header_by_default(): void
    {
        config(['site.noindex' => false]);

        $this->get('/')->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_unknown_page_shows_the_branded_public_layout(): void
    {
        $this->get('/halaman-tidak-ada')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('Back to home')
            ->assertSee('ICSMI');
    }

    public function test_full_responses_declare_their_length_so_proxies_can_finish_them(): void
    {
        config(['site.noindex' => false]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Length', (string) strlen("User-agent: *\nDisallow:\n"));
    }
}
