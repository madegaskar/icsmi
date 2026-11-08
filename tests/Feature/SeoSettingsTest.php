<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Article;
use App\Models\Issue;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(Role $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_save_site_seo_description_and_keywords(): void
    {
        $this->actingAs($this->userWith(Role::Admin))
            ->put(route('admin.seo.update'), [
                'seo_description' => 'Konferensi internasional manajemen berkelanjutan.',
                'seo_keywords' => 'manajemen, keberlanjutan, inovasi',
            ])
            ->assertRedirect(route('admin.seo.edit'));

        $this->assertSame('Konferensi internasional manajemen berkelanjutan.', SiteSetting::get('seo_description'));
        $this->assertSame('manajemen, keberlanjutan, inovasi', SiteSetting::get('seo_keywords'));
    }

    public function test_admin_seo_page_renders_with_saved_values(): void
    {
        SiteSetting::put('seo_description', 'Deskripsi tersimpan');

        $this->actingAs($this->userWith(Role::Admin))
            ->get(route('admin.seo.edit'))
            ->assertOk()
            ->assertSee('Deskripsi tersimpan');
    }

    public function test_editor_cannot_change_seo_settings(): void
    {
        $this->actingAs($this->userWith(Role::Editor))
            ->put(route('admin.seo.update'), ['seo_description' => 'Diubah editor'])
            ->assertForbidden();

        $this->assertNull(SiteSetting::get('seo_description'));
    }

    public function test_home_page_uses_site_seo_description_when_no_page_description(): void
    {
        SiteSetting::put('seo_description', 'Deskripsi SEO situs');

        $this->get('/')->assertOk()->assertSee('<meta name="description" content="Deskripsi SEO situs">', false);
    }

    public function test_article_page_has_scholarly_article_json_ld(): void
    {
        $issue = Issue::factory()->create(['title' => 'Proceedings 2026']);
        $article = Article::factory()->create([
            'issue_id' => $issue->id,
            'title' => 'Judul </script> Artikel',
            'doi' => '10.1234/icsmi.2026.001',
        ]);

        $this->get('/articles/'.$article->slug)
            ->assertOk()
            ->assertSee('<script type="application/ld+json">', false)
            ->assertSee('"@type":"ScholarlyArticle"', false)
            ->assertSee('"identifier":"10.1234\/icsmi.2026.001"', false)
            ->assertDontSee('</script> Artikel', false);
    }

    public function test_proceeding_page_has_publication_issue_json_ld(): void
    {
        $issue = Issue::factory()->create(['title' => 'Proceedings of ICSMI 2026']);

        $this->get('/archives/'.$issue->slug)
            ->assertOk()
            ->assertSee('"@type":"PublicationIssue"', false)
            ->assertSee('"name":"Proceedings of ICSMI 2026"', false);
    }
}
