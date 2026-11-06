<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\SidebarSection;
use App\Models\User;
use Database\Seeders\SidebarSectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarEditorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::Admin);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'LINK PENTING',
            'kind' => SidebarSection::KIND_LINKS,
            'sort_order' => 10,
            'is_visible' => '1',
            'body' => "Kontak Panitia | /contact\nPanduan | /author-guidelines",
        ];
    }

    public function test_admin_adds_a_section_and_it_shows_in_the_public_sidebar(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sidebar.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('LINK PENTING')
            ->assertSee('href="/contact"', false)
            ->assertSee('Kontak Panitia');
        $this->assertDatabaseHas('audit_logs', ['action' => 'sidebar.created', 'user_id' => $this->admin->id]);
    }

    public function test_editors_cannot_open_the_sidebar_editor(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::Editor);

        $this->actingAs($editor)->get(route('admin.sidebar.index'))->assertForbidden();
        $this->actingAs($editor)->post(route('admin.sidebar.store'), $this->payload())->assertForbidden();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function unsafeLines(): array
    {
        return [
            'javascript address' => ["Klik | javascript:alert(1)"],
            'http address' => ["Lama | http://contoh.ac.id"],
            'missing label' => ["| /contact"],
            'missing address' => ["Tanpa alamat |"],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unsafeLines')]
    public function test_links_must_have_a_label_and_a_safe_address(string $line): void
    {
        $this->actingAs($this->admin)->post(route('admin.sidebar.store'), $this->payload(['body' => $line]))
            ->assertSessionHasErrors('body');
        $this->assertDatabaseCount('sidebar_sections', 0);
    }

    public function test_hidden_sections_are_not_shown(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sidebar.store'), $this->payload([
            'title' => 'RAHASIA SIDEBAR',
            'is_visible' => '0',
        ]));

        $this->get('/')->assertDontSee('RAHASIA SIDEBAR');
    }

    public function test_text_sections_drop_raw_html(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sidebar.store'), $this->payload([
            'title' => 'CATATAN',
            'kind' => SidebarSection::KIND_TEXT,
            'body' => "Halo <script>alert(1)</script> dunia",
        ]))->assertSessionHasNoErrors();

        $this->get('/')
            ->assertSee('CATATAN')
            ->assertDontSee('<script>alert(1)', false);
    }

    public function test_the_default_sections_match_the_original_sidebar(): void
    {
        $this->seed(SidebarSectionSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('QUICK MENU')
            ->assertSee('INDEXING')
            ->assertSee('TEMPLATE')
            ->assertSee('ACCREDITATION')
            ->assertSee('VISITOR')
            ->assertSee('INFORMATION');
    }

    public function test_sections_can_be_reordered_by_their_number(): void
    {
        $this->seed(SidebarSectionSeeder::class);
        SidebarSection::where('title', 'INFORMATION')->update(['sort_order' => 0]);

        $html = $this->get('/')->getContent();

        $this->assertLessThan(strpos($html, '>QUICK MENU<'), strpos($html, '>INFORMATION<'));
    }
}
