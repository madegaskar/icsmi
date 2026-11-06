<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Issue;
use App\Models\Keyword;
use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function article(array $attributes = [], array $authors = ['Ada Lovelace'], array $keywords = []): Article
    {
        $article = Article::factory()->create($attributes);

        foreach ($authors as $position => $name) {
            $article->authors()->create(['name' => $name, 'affiliation' => 'Example University', 'position' => $position]);
        }

        $article->keywords()->sync(collect($keywords)->map(fn ($name) => Keyword::firstOrCreate(['name' => $name])->id));

        return $article;
    }

    public function test_public_pages_render(): void
    {
        $this->seed(PageSeeder::class);

        foreach (Page::SLUGS as $slug) {
            $this->get('/'.$slug)->assertOk();
        }

        foreach (['/', '/current', '/archives', '/editorial-board', '/speakers', '/announcements', '/search', '/sitemap.xml'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_unknown_page_slugs_return_not_found(): void
    {
        $this->get('/not-a-page')->assertNotFound();
    }

    public function test_page_markdown_strips_scripts_and_unsafe_links(): void
    {
        Page::create([
            'slug' => 'about',
            'title' => 'About',
            'body' => "Intro\n\n<script>alert(1)</script>\n\n<img src=x onerror=alert(1)>\n\n[click](javascript:alert(1))",
            'published_at' => now(),
        ]);

        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_article_page_shows_details_citations_and_scholar_tags(): void
    {
        $issue = Issue::factory()->create(['title' => 'Proceedings of ICSMI 2026', 'year' => 2026, 'isbn' => '978-0-00-000000-0']);
        $article = $this->article([
            'issue_id' => $issue->id,
            'title' => 'Green Finance',
            'doi' => '10.1234/icsmi.1',
            'first_page' => 3,
            'last_page' => 9,
            'pdf_path' => 'articles/a.pdf',
        ], ['Ada Lovelace', 'Alan Mathison Turing'], ['finance']);

        $response = $this->get('/articles/'.$article->slug)->assertOk();

        $response->assertSee('<meta name="citation_title" content="Green Finance">', false);
        $response->assertSee('<meta name="citation_author" content="Ada Lovelace">', false);
        $response->assertSee('<meta name="citation_author" content="Alan Mathison Turing">', false);
        $response->assertSee('<meta name="citation_author_institution" content="Example University">', false);
        $response->assertSee('<meta name="citation_conference_title" content="Proceedings of ICSMI 2026">', false);
        $response->assertSee('<meta name="citation_doi" content="10.1234/icsmi.1">', false);
        $response->assertSee('<meta name="citation_firstpage" content="3">', false);
        $response->assertSee('<meta name="citation_isbn" content="978-0-00-000000-0">', false);
        $response->assertSee('citation_pdf_url', false);

        $response->assertSeeText('Lovelace, A., & Turing, A. M. (2026). Green Finance. In Proceedings of ICSMI 2026 (pp. 3–9). https://doi.org/10.1234/icsmi.1');
        $response->assertSee('@inproceedings{lovelace2026'.$article->id, false);
        $response->assertSee('pages = {3--9}', false);

        $this->assertSame(1, (int) $article->stats()->sum('views'));
    }

    public function test_article_content_is_escaped_in_body_and_meta_tags(): void
    {
        $payload = '"><script>alert(1)</script>';
        $article = $this->article(['title' => $payload, 'abstract' => $payload], [$payload], [$payload]);

        $html = $this->get('/articles/'.$article->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)', $html);
        $this->assertStringContainsString(e($payload), $html);
    }

    public function test_unpublished_content_is_hidden(): void
    {
        $draftIssue = Issue::factory()->unpublished()->create(['title' => 'Draft Proceedings']);
        $inDraftIssue = $this->article(['issue_id' => $draftIssue->id, 'title' => 'In Draft Issue', 'pdf_path' => 'articles/a.pdf']);
        $draft = $this->article(['title' => 'Draft Article', 'published_at' => null, 'pdf_path' => 'articles/a.pdf']);
        $scheduled = $this->article(['title' => 'Scheduled Article', 'published_at' => now()->addWeek()]);

        Storage::fake('local');
        Storage::disk('local')->put('articles/a.pdf', '%PDF-1.4');

        foreach ([$inDraftIssue, $draft, $scheduled] as $article) {
            $this->get('/articles/'.$article->slug)->assertNotFound();
            $this->get('/articles/'.$article->slug.'/pdf')->assertNotFound();
        }

        $this->get('/archives/'.$draftIssue->slug)->assertNotFound();
        $this->get('/archives')->assertDontSee('Draft Proceedings');

        $sitemap = $this->get('/sitemap.xml')->assertOk();
        $sitemap->assertDontSee($draftIssue->slug);
        $sitemap->assertDontSee($draft->slug);
        $sitemap->assertDontSee($scheduled->slug);

        $this->get('/search?q=Draft')->assertDontSeeText('Draft Article');
        $this->get('/search?q=Scheduled')->assertDontSeeText('Scheduled Article');
    }

    public function test_sitemap_lists_published_urls(): void
    {
        $article = $this->article();

        $response = $this->get('/sitemap.xml')->assertOk();

        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee(route('articles.show', $article->slug));
        $response->assertSee(route('proceedings.show', $article->issue->slug));
        $this->assertNotFalse(simplexml_load_string($response->getContent()));
    }

    public function test_pdf_is_served_from_private_storage_and_counted(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('articles/a.pdf', '%PDF-1.4');

        $article = $this->article(['pdf_path' => 'articles/a.pdf']);

        $response = $this->get('/articles/'.$article->slug.'/pdf')->assertOk();

        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame(1, (int) $article->stats()->sum('downloads'));
    }

    public function test_missing_pdf_returns_not_found_and_is_not_counted(): void
    {
        Storage::fake('local');

        $article = $this->article(['pdf_path' => 'articles/missing.pdf']);

        $this->get('/articles/'.$article->slug.'/pdf')->assertNotFound();
        $this->assertSame(0, (int) $article->stats()->sum('downloads'));
    }

    public function test_search_filters_by_title_author_keyword_and_year(): void
    {
        $issue2024 = Issue::factory()->create(['year' => 2024]);
        $issue2026 = Issue::factory()->create(['year' => 2026]);
        $this->article(['issue_id' => $issue2024->id, 'title' => 'Circular Economy'], ['Grace Hopper'], ['recycling']);
        $this->article(['issue_id' => $issue2026->id, 'title' => 'Digital Innovation'], ['Katherine Johnson'], ['technology']);

        $this->get('/search?q=circular')->assertSeeText('Circular Economy')->assertDontSeeText('Digital Innovation');
        $this->get('/search?author=Johnson')->assertSeeText('Digital Innovation')->assertDontSeeText('Circular Economy');
        $this->get('/search?keyword=recycling')->assertSeeText('Circular Economy')->assertDontSeeText('Digital Innovation');
        $this->get('/search?year=2026')->assertSeeText('Digital Innovation')->assertDontSeeText('Circular Economy');
    }

    public function test_search_treats_wildcards_and_sql_as_literal_text(): void
    {
        $this->article(['title' => 'Plain Title']);
        $this->article(['title' => 'Growth of 100% Recycling']);

        $this->get('/search?q=%25')->assertSeeText('Growth of 100% Recycling')->assertDontSeeText('Plain Title');
        $this->get('/search?q=_')->assertDontSeeText('Plain Title');

        foreach (["' OR '1'='1", "x%' OR 1=1 --", "'; DROP TABLE articles; --", "x') UNION SELECT password FROM users --"] as $payload) {
            $this->get('/search?'.http_build_query(['q' => $payload]))
                ->assertOk()
                ->assertDontSeeText('Plain Title');
        }

        $this->assertDatabaseCount('articles', 2);
    }

    public function test_search_validates_input_and_escapes_it(): void
    {
        $this->get('/search?year=abc')->assertSessionHasErrors('year');
        $this->get('/search?year=1%20OR%201=1')->assertSessionHasErrors('year');
        $this->get('/search?q[]=array')->assertSessionHasErrors('q');

        $html = $this->get('/search?'.http_build_query(['q' => '"><script>alert(1)</script>']))->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)', $html);
    }
}
