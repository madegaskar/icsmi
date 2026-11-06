<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\HomeBlock;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::Admin);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function heroPayload(array $overrides = []): array
    {
        return $overrides + [
            'type' => HomeBlock::TYPE_HERO,
            'sort_order' => 1,
            'is_visible' => '1',
            'heading' => 'Selamat Datang di ICSMI',
            'subtitle' => 'ICSMI',
        ];
    }

    public function test_admin_can_upload_the_hero_image_from_the_block_form(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->heroPayload([
            'desktop_image' => UploadedFile::fake()->image('hero.jpg', 2400, 1000),
            'image_alt' => 'Peserta konferensi di aula utama',
        ]));

        $response->assertSessionHasNoErrors()->assertRedirect();

        $block = HomeBlock::where('type', HomeBlock::TYPE_HERO)->firstOrFail();
        $media = Media::sole();
        $this->assertSame($media->id, $block->desktop_media_id);
        $this->assertSame('Peserta konferensi di aula utama', $media->alt_text);
        $this->assertSame(Media::KIND_IMAGE, $media->kind);
    }

    public function test_uploading_a_hero_image_requires_alt_text(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->heroPayload([
            'desktop_image' => UploadedFile::fake()->image('hero.jpg', 1200, 600),
            'image_alt' => '',
        ]));

        $response->assertSessionHasErrors('image_alt');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_a_disguised_file_is_refused_as_hero_image(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->heroPayload([
            'desktop_image' => UploadedFile::fake()->createWithContent('hero.jpg', '<?php echo 1; ?>'),
            'image_alt' => 'Hero',
        ]));

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('home_blocks', 0);
    }

    public function test_hero_shows_the_image_on_half_the_width_under_a_gradient_overlay(): void
    {
        $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->heroPayload([
            'desktop_image' => UploadedFile::fake()->image('hero.jpg', 1200, 600),
            'image_alt' => 'Foto hero',
        ]));
        $media = Media::sole();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('lg:grid-cols-2', $html);
        $this->assertStringContainsString('data-hero-image', $html);
        $this->assertStringContainsString('/media/'.$media->uuid.'/desktop', $html);
        $this->assertStringContainsString('data-hero-overlay', $html);
        $this->assertStringContainsString('bg-gradient-to-r', $html);
    }

    public function test_hero_without_an_image_keeps_the_text_only_layout(): void
    {
        $this->actingAs($this->admin)->post(route('admin.home-blocks.store'), $this->heroPayload());

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('data-hero-image', $html);
        $this->assertStringContainsString('Selamat Datang di ICSMI', $html);
    }

    public function test_editors_cannot_upload_hero_images(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole(Role::Editor);

        $this->actingAs($editor)->post(route('admin.home-blocks.store'), $this->heroPayload([
            'desktop_image' => UploadedFile::fake()->image('hero.jpg', 1200, 600),
            'image_alt' => 'Hero',
        ]))->assertForbidden();
    }
}
