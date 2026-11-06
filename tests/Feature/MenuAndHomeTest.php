<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\HomeBlock;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\User;
use Database\Seeders\HomeBlockSeeder;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuAndHomeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

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
    private function menuPayload(array $overrides = []): array
    {
        return $overrides + [
            'location' => MenuItem::LOCATION_HEADER,
            'label' => 'Tentang',
            'link_type' => MenuItem::TYPE_INTERNAL,
            'url' => '/about',
            'parent_id' => null,
            'media_id' => null,
            'sort_order' => 1,
            'is_visible' => '1',
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function blockPayload(array $overrides = []): array
    {
        return $overrides + [
            'type' => HomeBlock::TYPE_TEXT,
            'sort_order' => 4,
            'is_visible' => '1',
            'starts_at' => null,
            'ends_at' => null,
            'heading' => 'Blok teks',
            'subtitle' => null,
            'body' => 'Isi blok.',
            'button_label' => null,
            'button_url' => null,
            'desktop_media_id' => null,
            'mobile_media_id' => null,
        ];
    }

    private function imageMedia(): Media
    {
        $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('foto.jpg', 1200, 600),
            'alt_text' => 'Foto',
        ]);

        return Media::latest('id')->firstOrFail();
    }

    private function pdfMedia(): Media
    {
        $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => UploadedFile::fake()->createWithContent('panduan.pdf', "%PDF-1.4\n"),
        ]);

        return Media::latest('id')->firstOrFail();
    }

    // Menu

    public function test_admin_can_add_a_header_link_that_appears_on_the_public_site(): void
    {
        $this->actingAs($this->admin)->post(route('admin.menu.store'), $this->menuPayload([
            'label' => 'Panduan Penulis Baru',
        ]))->assertRedirect();

        $this->get('/')->assertSee('Panduan Penulis Baru');
    }

    public function test_hidden_menu_items_are_not_shown_publicly(): void
    {
        MenuItem::create(['location' => 'header', 'label' => 'Rahasia Menu', 'link_type' => 'internal', 'url' => '/about', 'is_visible' => false]);

        $this->get('/')->assertDontSee('Rahasia Menu');
    }

    public function test_menu_labels_are_escaped_on_the_public_site(): void
    {
        $this->actingAs($this->admin)->post(route('admin.menu.store'), $this->menuPayload([
            'label' => '<script>alert(1)</script>',
        ]))->assertRedirect();

        $this->get('/')->assertDontSee('<script>alert(1)</script>', false);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function unsafeLinks(): array
    {
        return [
            'javascript scheme' => ['javascript:alert(1)'],
            'data scheme' => ['data:text/html,<script>alert(1)</script>'],
            'vbscript scheme' => ['vbscript:msgbox(1)'],
            'plain http' => ['http://contoh.ac.id'],
            'protocol relative' => ['//contoh.ac.id'],
            'space and markup' => ['/about"><script>'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unsafeLinks')]
    public function test_unsafe_menu_links_are_rejected(string $url): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.menu.store'), $this->menuPayload([
            'link_type' => MenuItem::TYPE_EXTERNAL,
            'url' => $url,
        ]));

        $response->assertSessionHasErrors('url');
        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_https_and_mailto_external_links_are_accepted(): void
    {
        $this->actingAs($this->admin)->post(route('admin.menu.store'), $this->menuPayload([
            'link_type' => MenuItem::TYPE_EXTERNAL,
            'url' => 'https://contoh.ac.id/registrasi',
        ]))->assertRedirect();

        $this->actingAs($this->admin)->post(route('admin.menu.store'), $this->menuPayload([
            'label' => 'Kontak',
            'link_type' => MenuItem::TYPE_EXTERNAL,
            'url' => 'mailto:panitia@contoh.ac.id',
            'sort_order' => 2,
        ]))->assertRedirect();

        $this->assertDatabaseCount('menu_items', 2);
    }

    public function test_menu_allows_only_one_level_of_submenu(): void
    {
        $parent = MenuItem::create(['location' => 'header', 'label' => 'Induk', 'link_type' => 'internal', 'url' => '/about']);
        $child = MenuItem::create(['location' => 'header', 'label' => 'Anak', 'link_type' => 'internal', 'url' => '/scope', 'parent_id' => $parent->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.menu.store'), $this->menuPayload([
            'label' => 'Cucu',
            'parent_id' => $child->id,
        ]));

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_menu_item_with_children_cannot_become_a_child(): void
    {
        $parent = MenuItem::create(['location' => 'header', 'label' => 'Induk', 'link_type' => 'internal', 'url' => '/about']);
        MenuItem::create(['location' => 'header', 'label' => 'Anak', 'link_type' => 'internal', 'url' => '/scope', 'parent_id' => $parent->id]);
        $other = MenuItem::create(['location' => 'header', 'label' => 'Lain', 'link_type' => 'internal', 'url' => '/contact']);

        $response = $this->actingAs($this->admin)->put(route('admin.menu.update', $parent), $this->menuPayload([
            'label' => 'Induk',
            'parent_id' => $other->id,
        ]));

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_menu_media_link_points_to_the_file_and_disappears_when_the_file_is_deleted(): void
    {
        $pdf = $this->pdfMedia();

        $this->actingAs($this->admin)->post(route('admin.menu.store'), $this->menuPayload([
            'label' => 'Panduan PDF',
            'link_type' => MenuItem::TYPE_MEDIA,
            'url' => null,
            'media_id' => $pdf->id,
        ]))->assertRedirect();

        $this->get('/')->assertSee('/media/'.$pdf->uuid.'/original', false);

        $this->actingAs($this->admin)->delete(route('library.destroy', $pdf));

        $this->get('/')->assertDontSee('Panduan PDF');
    }

    public function test_editor_cannot_manage_the_menu(): void
    {
        $this->actingAs($this->editor)->get(route('admin.menu.index'))->assertForbidden();
        $this->actingAs($this->editor)->post(route('admin.menu.store'), $this->menuPayload())->assertForbidden();
    }

    public function test_menu_seeder_fills_defaults_once_and_never_overwrites_edits(): void
    {
        $this->seed(MenuSeeder::class);
        $count = MenuItem::count();

        $this->seed(MenuSeeder::class);

        $this->assertGreaterThan(0, $count);
        $this->assertSame($count, MenuItem::count());
    }

    // Home blocks

    public function test_admin_can_reorder_blocks_with_the_sort_number(): void
    {
        $this->seed(HomeBlockSeeder::class);
        $announcements = HomeBlock::where('type', HomeBlock::TYPE_ANNOUNCEMENTS)->firstOrFail();
        $announcements->update(['sort_order' => 0]);

        $html = $this->get('/')->getContent();

        $this->assertLessThan(
            strpos($html, 'data-block="proceedings"'),
            strpos($html, 'data-block="announcements"'),
        );
    }

    public function test_block_scheduled_for_the_future_is_hidden_until_its_start_time(): void
    {
        $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->blockPayload([
            'heading' => 'Pendaftaran Dibuka',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]))->assertRedirect();

        $this->get('/')->assertDontSee('Pendaftaran Dibuka');

        $this->travel(2)->days();

        $this->get('/')->assertSee('Pendaftaran Dibuka');
    }

    public function test_block_is_hidden_after_its_end_time(): void
    {
        HomeBlock::create([
            'type' => HomeBlock::TYPE_TEXT,
            'sort_order' => 4,
            'is_visible' => true,
            'ends_at' => now()->subHour(),
            'heading' => 'Sudah Berakhir',
            'body' => 'Isi.',
        ]);

        $this->get('/')->assertDontSee('Sudah Berakhir');
    }

    public function test_text_block_markdown_strips_scripts_and_unsafe_links(): void
    {
        $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->blockPayload([
            'heading' => 'Blok Aman',
            'body' => "<script>alert(1)</script>\n\n[klik](javascript:alert(1))",
        ]))->assertRedirect();

        $this->get('/')
            ->assertSee('Blok Aman')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:alert', false);
    }

    public function test_hero_image_must_be_an_image_not_a_pdf(): void
    {
        $pdf = $this->pdfMedia();

        $response = $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->blockPayload([
            'type' => HomeBlock::TYPE_HERO,
            'heading' => 'Hero',
            'desktop_media_id' => $pdf->id,
        ]));

        $response->assertSessionHasErrors('desktop_media_id');
    }

    public function test_hero_button_link_follows_the_same_rules_as_menu_links(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->blockPayload([
            'type' => HomeBlock::TYPE_HERO,
            'heading' => 'Hero',
            'button_label' => 'Klik',
            'button_url' => 'javascript:alert(1)',
        ]));

        $response->assertSessionHasErrors('button_url');
    }

    public function test_only_one_hero_block_is_allowed(): void
    {
        HomeBlock::create(['type' => HomeBlock::TYPE_HERO, 'sort_order' => 1, 'is_visible' => true, 'heading' => 'Pertama']);

        $response = $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->blockPayload([
            'type' => HomeBlock::TYPE_HERO,
            'heading' => 'Kedua',
        ]));

        $response->assertSessionHasErrors('type');
    }

    public function test_editor_cannot_manage_home_blocks(): void
    {
        $this->actingAs($this->editor)->get(route('admin.home-blocks.index'))->assertForbidden();
        $this->actingAs($this->editor)->post(route('admin.home-blocks.store'), $this->blockPayload())->assertForbidden();
    }

    public function test_hero_image_appears_on_the_public_home_page(): void
    {
        $image = $this->imageMedia();
        $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->blockPayload([
            'type' => HomeBlock::TYPE_HERO,
            'heading' => 'Selamat Datang',
            'desktop_media_id' => $image->id,
            'mobile_media_id' => $image->id,
        ]))->assertRedirect();

        $this->get('/')
            ->assertSee('Selamat Datang')
            ->assertSee('/media/'.$image->uuid.'/desktop', false);
    }

    public function test_home_block_changes_are_audited(): void
    {
        $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->blockPayload());
        $block = HomeBlock::firstOrFail();

        $this->actingAs($this->admin)->delete(route('admin.home-blocks.destroy', $block));

        $this->assertDatabaseHas('audit_logs', ['action' => 'home_block.created', 'user_id' => $this->admin->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'home_block.deleted', 'user_id' => $this->admin->id]);
    }

    public function test_home_block_seeder_fills_defaults_once(): void
    {
        $this->seed(HomeBlockSeeder::class);
        $count = HomeBlock::count();

        $this->seed(HomeBlockSeeder::class);

        $this->assertSame($count, HomeBlock::count());
        $this->assertGreaterThan(0, $count);
    }
}
