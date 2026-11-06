<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\HomeBlock;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnnouncementsAndContentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->userWith(Role::Admin);
        $this->editor = $this->userWith(Role::Editor);
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
    private function announcementPayload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Batas Pengumpulan Naskah',
            'body' => 'Naskah lengkap harus dikirim sebelum tanggal tenggat.',
            'category' => Announcement::CATEGORY_DEADLINE,
            'published' => '1',
            'ends_at' => null,
        ];
    }

    // Announcements

    public function test_announcements_can_be_filtered_by_category_on_the_public_site(): void
    {
        $this->actingAs($this->editor)->post(route('admin.announcements.store'), $this->announcementPayload([
            'title' => 'Tenggat Naskah Final',
            'category' => Announcement::CATEGORY_DEADLINE,
        ]))->assertSessionHasNoErrors();
        $this->actingAs($this->editor)->post(route('admin.announcements.store'), $this->announcementPayload([
            'title' => 'Berita Keynote Baru',
            'category' => Announcement::CATEGORY_NEWS,
        ]))->assertSessionHasNoErrors();

        $this->get('/announcements?category=deadline')
            ->assertOk()
            ->assertSee('Tenggat Naskah Final')
            ->assertDontSee('Berita Keynote Baru');

        $this->get('/announcements?category=tidak-ada')->assertNotFound();
    }

    public function test_expired_and_unpublished_announcements_are_hidden(): void
    {
        Announcement::create([
            'title' => 'Sudah Lewat',
            'body' => 'Isi pengumuman yang sudah lewat.',
            'category' => Announcement::CATEGORY_NEWS,
            'published_at' => now()->subDays(3),
            'ends_at' => now()->subDay(),
        ]);
        Announcement::create([
            'title' => 'Belum Terbit',
            'body' => 'Isi pengumuman yang belum terbit.',
            'category' => Announcement::CATEGORY_NEWS,
            'published_at' => null,
        ]);

        $this->get('/announcements')->assertDontSee('Sudah Lewat')->assertDontSee('Belum Terbit');
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function invalidAnnouncementFields(): array
    {
        return [
            'title too short' => ['title', 'Hai'],
            'title with a control character' => ['title', "Judul\x07 yang rusak"],
            'body too short' => ['body', 'Pendek'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidAnnouncementFields')]
    public function test_announcement_title_and_body_must_pass_length_and_character_checks(string $field, string $value): void
    {
        $response = $this->actingAs($this->editor)->post(route('admin.announcements.store'), $this->announcementPayload([$field => $value]));

        $response->assertSessionHasErrors($field);
        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_end_date_cannot_be_in_the_past(): void
    {
        $response = $this->actingAs($this->editor)->post(route('admin.announcements.store'), $this->announcementPayload([
            'ends_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ]));

        $response->assertSessionHasErrors('ends_at');
    }

    public function test_editors_manage_announcements_but_authors_cannot(): void
    {
        $author = $this->userWith(Role::Author);

        $this->actingAs($this->editor)->get(route('admin.announcements.index'))->assertOk();
        $this->actingAs($author)->get(route('admin.announcements.index'))->assertForbidden();
    }

    // Export

    public function test_export_is_for_admins_only_and_contains_no_accounts(): void
    {
        $this->actingAs($this->editor)->get(route('admin.content.export'))->assertForbidden();

        $response = $this->actingAs($this->admin)->get(route('admin.content.export'))->assertOk();
        $body = $response->getContent();

        $this->assertSame('icsmi-content', json_decode($body, true)['format']);
        $this->assertStringNotContainsString($this->admin->email, $body);
        $this->assertStringNotContainsString('password', strtolower($body));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    // Import

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'format' => 'icsmi-content',
            'version' => 1,
            'pages' => [],
            'announcements' => [],
            'menu_items' => [],
            'home_blocks' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function uploadImport(array $payload, ?User $as = null): TestResponse
    {
        $file = UploadedFile::fake()->createWithContent('isi.json', json_encode($payload, JSON_THROW_ON_ERROR));

        return $this->actingAs($as ?? $this->admin)->post(route('admin.content.import'), ['file' => $file]);
    }

    private function tokenFrom(TestResponse $response): string
    {
        $location = $response->headers->get('Location');
        $response->assertRedirect();

        return Str::afterLast($location, '/');
    }

    public function test_import_only_previews_changes_until_the_admin_confirms(): void
    {
        $payload = $this->payload(['pages' => [[
            'slug' => 'tentang-tim',
            'title' => 'Tentang Tim',
            'body' => 'Tim penyelenggara ICSMI.',
            'published_at' => now()->toIso8601String(),
        ]]]);

        $token = $this->tokenFrom($this->uploadImport($payload));

        $this->assertDatabaseMissing('pages', ['slug' => 'tentang-tim']);
        $this->actingAs($this->admin)->get(route('admin.content.preview', $token))
            ->assertOk()
            ->assertSee('Tentang Tim')
            ->assertSee('Create');

        $this->actingAs($this->admin)->post(route('admin.content.apply', $token))->assertRedirect();

        $page = Page::where('slug', 'tentang-tim')->firstOrFail();
        $this->assertSame('Tim penyelenggara ICSMI.', $page->body);
        $this->assertSame(1, PageRevision::where('page_id', $page->id)->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'content.imported', 'user_id' => $this->admin->id]);
    }

    public function test_import_updates_an_existing_page_with_the_same_address(): void
    {
        $page = Page::create(['slug' => 'program', 'title' => 'Program Lama', 'body' => 'Isi lama.', 'published_at' => now()]);

        $token = $this->tokenFrom($this->uploadImport($this->payload(['pages' => [[
            'slug' => 'program',
            'title' => 'Program Baru',
            'body' => 'Isi baru.',
            'published_at' => $page->published_at->toIso8601String(),
        ]]])));

        $this->actingAs($this->admin)->post(route('admin.content.apply', $token));

        $this->assertSame('Program Baru', $page->fresh()->title);
        $this->assertSame(1, PageRevision::where('page_id', $page->id)->count());
    }

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function rejectedImports(): array
    {
        return [
            'wrong format' => [['format' => 'lain', 'version' => 1, 'pages' => []]],
            'wrong version' => [['format' => 'icsmi-content', 'version' => 99, 'pages' => []]],
            'html in a page body' => [['format' => 'icsmi-content', 'version' => 1, 'pages' => [[
                'slug' => 'contoh', 'title' => 'Contoh', 'body' => 'Halo <script>alert(1)</script>', 'published_at' => null,
            ]]]],
            'unsafe link in a block' => [['format' => 'icsmi-content', 'version' => 1, 'home_blocks' => [[
                'type' => 'hero', 'sort_order' => 1, 'is_visible' => true, 'heading' => 'Hero', 'button_label' => 'Klik', 'button_url' => 'javascript:alert(1)',
            ]]]],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('rejectedImports')]
    public function test_import_rejects_wrong_formats_html_and_unsafe_links(array $payload): void
    {
        $response = $this->uploadImport($payload);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('pages', 0);
    }

    public function test_import_is_blocked_when_it_refers_to_a_missing_file(): void
    {
        $payload = $this->payload(['home_blocks' => [[
            'type' => HomeBlock::TYPE_HERO,
            'sort_order' => 1,
            'is_visible' => true,
            'heading' => 'Hero',
            'desktop_media_uuid' => (string) Str::uuid(),
        ]]]);

        $token = $this->tokenFrom($this->uploadImport($payload));

        $this->actingAs($this->admin)->get(route('admin.content.preview', $token))
            ->assertOk()
            ->assertSee('missing from the Media Library');

        $this->actingAs($this->admin)->post(route('admin.content.apply', $token))->assertSessionHasErrors();
        $this->assertDatabaseCount('home_blocks', 0);
    }

    public function test_a_preview_can_only_be_applied_by_the_admin_who_uploaded_it(): void
    {
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole(Role::Admin);
        $token = $this->tokenFrom($this->uploadImport($this->payload(['pages' => [[
            'slug' => 'rahasia', 'title' => 'Rahasia', 'body' => 'Isi.', 'published_at' => null,
        ]]])));

        $this->actingAs($otherAdmin)->post(route('admin.content.apply', $token))->assertNotFound();
        $this->assertDatabaseMissing('pages', ['slug' => 'rahasia']);
    }

    public function test_exported_content_imports_back_without_creating_duplicates(): void
    {
        Page::create(['slug' => 'scope', 'title' => 'Ruang Lingkup', 'body' => 'Isi ruang lingkup.', 'published_at' => now()]);
        Announcement::create(['title' => 'Pengumuman Tetap', 'body' => 'Isi pengumuman tetap.', 'category' => Announcement::CATEGORY_NEWS, 'published_at' => now()]);

        $exported = $this->actingAs($this->admin)->get(route('admin.content.export'))->getContent();
        $token = $this->tokenFrom($this->uploadImport(json_decode($exported, true)));

        $this->actingAs($this->admin)->get(route('admin.content.preview', $token))
            ->assertOk()
            ->assertDontSee('Create');

        $this->actingAs($this->admin)->post(route('admin.content.apply', $token));

        $this->assertSame(1, Page::where('slug', 'scope')->count());
        $this->assertSame(1, Announcement::where('title', 'Pengumuman Tetap')->count());
    }

    public function test_import_replaces_menu_and_home_blocks_and_is_audited(): void
    {
        MenuItem::create(['location' => 'header', 'label' => 'Lama', 'link_type' => 'internal', 'url' => '/about']);

        $token = $this->tokenFrom($this->uploadImport($this->payload([
            'menu_items' => [
                ['id' => 10, 'parent_id' => null, 'location' => 'header', 'label' => 'Baru', 'link_type' => 'internal', 'url' => '/scope', 'sort_order' => 1, 'is_visible' => true],
                ['id' => 11, 'parent_id' => 10, 'location' => 'header', 'label' => 'Anak', 'link_type' => 'internal', 'url' => '/program', 'sort_order' => 1, 'is_visible' => true],
            ],
            'home_blocks' => [[
                'type' => HomeBlock::TYPE_TEXT, 'sort_order' => 1, 'is_visible' => true, 'heading' => 'Blok Baru', 'body' => 'Isi blok.',
            ]],
        ])));

        $this->actingAs($this->admin)->post(route('admin.content.apply', $token));

        $this->assertDatabaseMissing('menu_items', ['label' => 'Lama']);
        $child = MenuItem::where('label', 'Anak')->firstOrFail();
        $this->assertSame(MenuItem::where('label', 'Baru')->value('id'), $child->parent_id);
        $this->assertDatabaseCount('home_blocks', 1);
        $this->assertSame(1, AuditLog::where('action', 'content.imported')->count());
    }

    public function test_import_files_over_two_megabytes_are_refused(): void
    {
        $file = UploadedFile::fake()->create('besar.json', 2049, 'application/json');

        $this->actingAs($this->admin)->post(route('admin.content.import'), ['file' => $file])->assertSessionHasErrors('file');
    }

    public function test_editors_cannot_import(): void
    {
        $this->uploadImport($this->payload(), $this->editor)->assertForbidden();
    }
}
