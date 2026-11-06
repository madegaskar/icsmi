<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\TestCase;

class PagesAndRevisionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $editor;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = $this->userWith(Role::Admin);
        $this->editor = $this->userWith(Role::Editor);
        $this->author = $this->userWith(Role::Author);
    }

    private function userWith(Role $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function pagePayload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Panduan Penulis',
            'slug' => '',
            'body' => 'Isi halaman.',
            'visibility' => 'draft',
            'publish_at' => '',
        ];
    }

    private function createPage(array $overrides = [], ?User $as = null): Page
    {
        $this->actingAs($as ?? $this->editor)->post(route('admin.pages.store'), $this->pagePayload($overrides))
            ->assertSessionHasNoErrors();

        return Page::latest('id')->firstOrFail();
    }

    // Creating and visibility

    public function test_editor_and_admin_can_create_pages_but_authors_cannot(): void
    {
        $this->actingAs($this->editor)->post(route('admin.pages.store'), $this->pagePayload(['title' => 'Dari Editor']))
            ->assertRedirect(route('admin.pages.index'));
        $this->actingAs($this->admin)->post(route('admin.pages.store'), $this->pagePayload(['title' => 'Dari Admin']))
            ->assertRedirect(route('admin.pages.index'));
        $this->actingAs($this->author)->post(route('admin.pages.store'), $this->pagePayload(['title' => 'Dari Author']))
            ->assertForbidden();

        $this->assertDatabaseCount('pages', 2);
    }

    public function test_slug_is_made_from_the_title_when_left_empty(): void
    {
        $page = $this->createPage(['title' => 'Jadwal Konferensi 2026']);

        $this->assertSame('jadwal-konferensi-2026', $page->slug);
    }

    public function test_draft_page_is_hidden_from_visitors(): void
    {
        $page = $this->createPage(['title' => 'Rahasia', 'visibility' => 'draft']);

        $this->assertNull($page->published_at);
        $this->get('/'.$page->slug)->assertNotFound();
    }

    public function test_published_page_is_visible_and_scheduled_page_appears_at_its_time(): void
    {
        $now = $this->createPage(['title' => 'Terbit Sekarang', 'visibility' => 'publish']);
        $later = $this->createPage(['title' => 'Terjadwal', 'visibility' => 'publish', 'publish_at' => now()->addDay()->format('Y-m-d\TH:i')]);

        $this->get('/'.$now->slug)->assertOk()->assertSee('Terbit Sekarang');
        $this->get('/'.$later->slug)->assertNotFound();

        $this->travel(2)->days();

        $this->get('/'.$later->slug)->assertOk()->assertSee('Terjadwal');
    }

    public function test_changing_the_slug_moves_the_page_and_the_old_address_stops_working(): void
    {
        $page = $this->createPage(['title' => 'Kebijakan Etika', 'visibility' => 'publish']);

        $this->actingAs($this->editor)->put(route('admin.pages.update', $page), $this->pagePayload([
            'title' => 'Kebijakan Etika',
            'slug' => 'etika-publikasi',
            'visibility' => 'publish',
        ]))->assertSessionHasNoErrors();

        $this->get('/kebijakan-etika')->assertNotFound();
        $this->get('/etika-publikasi')->assertOk();
    }

    public function test_slug_must_be_unique_even_against_deleted_pages(): void
    {
        $page = $this->createPage(['title' => 'Kontak Panitia', 'slug' => 'kontak-panitia']);
        $this->actingAs($this->admin)->delete(route('admin.pages.destroy', $page));

        $response = $this->actingAs($this->editor)->post(route('admin.pages.store'), $this->pagePayload([
            'title' => 'Kontak Lain',
            'slug' => 'kontak-panitia',
        ]));

        $response->assertSessionHasErrors('slug');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidSlugs(): array
    {
        return [
            'reserved admin path' => ['admin'],
            'reserved proceedings path' => ['current'],
            'reserved login path' => ['login'],
            'uppercase letters' => ['Tentang'],
            'spaces' => ['tentang kami'],
            'path separator' => ['tentang/kami'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidSlugs')]
    public function test_reserved_or_malformed_slugs_are_rejected(string $slug): void
    {
        $response = $this->actingAs($this->editor)->post(route('admin.pages.store'), $this->pagePayload(['slug' => $slug]));

        $response->assertSessionHasErrors('slug');
        $this->assertDatabaseCount('pages', 0);
    }

    // Revisions

    public function test_every_save_creates_a_revision_and_the_history_lists_them(): void
    {
        $page = $this->createPage(['title' => 'Versi Awal', 'body' => 'Teks pertama']);

        $this->actingAs($this->editor)->put(route('admin.pages.update', $page), $this->pagePayload([
            'title' => 'Versi Kedua',
            'body' => 'Teks kedua',
        ]))->assertSessionHasNoErrors();

        $this->assertSame(2, PageRevision::where('page_id', $page->id)->count());

        $this->actingAs($this->editor)->get(route('admin.pages.revisions', $page))
            ->assertOk()
            ->assertSee('Versi Awal')
            ->assertSee('Versi Kedua');
    }

    public function test_restoring_a_revision_brings_back_its_content_and_keeps_history_intact(): void
    {
        $page = $this->createPage(['title' => 'Asli', 'body' => 'Isi asli']);
        $original = PageRevision::where('page_id', $page->id)->firstOrFail();

        $this->actingAs($this->editor)->put(route('admin.pages.update', $page), $this->pagePayload([
            'title' => 'Diubah',
            'body' => 'Isi yang salah',
        ]));

        $this->actingAs($this->editor)->post(route('admin.pages.revisions.restore', [$page, $original]))
            ->assertSessionHasNoErrors();

        $page->refresh();
        $this->assertSame('Asli', $page->title);
        $this->assertSame('Isi asli', $page->body);
        $this->assertSame(3, PageRevision::where('page_id', $page->id)->count());
        $this->assertSame('Isi asli', $original->fresh()->body);
    }

    public function test_a_revision_of_another_page_cannot_be_restored_here(): void
    {
        $first = $this->createPage(['title' => 'Halaman Satu']);
        $second = $this->createPage(['title' => 'Halaman Dua']);
        $foreign = PageRevision::where('page_id', $second->id)->firstOrFail();

        $this->actingAs($this->editor)->post(route('admin.pages.revisions.restore', [$first, $foreign]))
            ->assertNotFound();
    }

    public function test_revisions_cannot_be_changed_or_deleted(): void
    {
        $page = $this->createPage(['title' => 'Tetap']);
        $revision = PageRevision::where('page_id', $page->id)->firstOrFail();

        $this->expectException(LogicException::class);
        $revision->update(['body' => 'Diubah diam-diam']);
    }

    public function test_revisions_cannot_be_deleted(): void
    {
        $page = $this->createPage(['title' => 'Tetap']);
        $revision = PageRevision::where('page_id', $page->id)->firstOrFail();

        $this->expectException(LogicException::class);
        $revision->delete();
    }

    public function test_deleting_a_page_hides_it_but_keeps_its_history(): void
    {
        $page = $this->createPage(['title' => 'Akan Dihapus', 'visibility' => 'publish']);
        $revisionCount = PageRevision::where('page_id', $page->id)->count();

        $this->actingAs($this->admin)->delete(route('admin.pages.destroy', $page))->assertRedirect();

        $this->get('/'.$page->slug)->assertNotFound();
        $this->assertSame($revisionCount, PageRevision::where('page_id', $page->id)->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'page.deleted', 'user_id' => $this->admin->id]);
    }

    public function test_page_changes_are_audited(): void
    {
        $page = $this->createPage(['title' => 'Dicatat']);
        $this->actingAs($this->editor)->put(route('admin.pages.update', $page), $this->pagePayload(['title' => 'Dicatat Ulang']));

        $this->assertDatabaseHas('audit_logs', ['action' => 'page.created', 'user_id' => $this->editor->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'page.updated', 'user_id' => $this->editor->id]);
        $this->assertSame(2, AuditLog::where('action', 'like', 'page.%')->count());
    }

    // Preview and content

    public function test_preview_is_for_staff_only_and_is_never_indexed(): void
    {
        $page = $this->createPage(['title' => 'Pratinjau', 'visibility' => 'draft']);

        $this->app['auth']->forgetGuards();
        $this->get(route('admin.pages.preview', $page))->assertRedirect(route('login'));
        $this->actingAs($this->author)->get(route('admin.pages.preview', $page))->assertForbidden();

        $this->actingAs($this->editor)->get(route('admin.pages.preview', $page))
            ->assertOk()
            ->assertSee('Pratinjau')
            ->assertSee('name="robots" content="noindex', false);
    }

    public function test_scripts_and_unsafe_links_in_editor_content_are_removed_on_the_public_page(): void
    {
        $page = $this->createPage([
            'title' => 'Aman',
            'body' => "## Judul\n\n<script>alert(1)</script>\n\n[klik](javascript:alert(1))",
            'visibility' => 'publish',
        ]);

        $this->get('/'.$page->slug)
            ->assertOk()
            ->assertSee('<h2>Judul</h2>', false)
            ->assertDontSee('<script>alert(1)', false)
            ->assertDontSee('javascript:alert', false);
    }

    public function test_form_offers_markdown_for_library_images_but_not_pdfs(): void
    {
        $this->actingAs($this->editor)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('ruang.jpg', 400, 300),
            'alt_text' => 'Ruang sidang',
        ]);
        $image = Media::where('kind', Media::KIND_IMAGE)->firstOrFail();
        $this->actingAs($this->editor)->post(route('library.store'), [
            'file' => UploadedFile::fake()->createWithContent('panduan-unik.pdf', "%PDF-1.4\n"),
        ]);

        $this->actingAs($this->editor)->get(route('admin.pages.create'))
            ->assertOk()
            ->assertSee('![Ruang sidang](/media/'.$image->uuid.'/desktop)', false)
            ->assertDontSee('panduan-unik.pdf');
    }

    public function test_sitemap_lists_only_published_pages(): void
    {
        $this->createPage(['title' => 'Terlihat', 'slug' => 'terlihat', 'visibility' => 'publish']);
        $this->createPage(['title' => 'Tersembunyi', 'slug' => 'tersembunyi', 'visibility' => 'draft']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/terlihat')
            ->assertDontSee('/tersembunyi');
    }
}
