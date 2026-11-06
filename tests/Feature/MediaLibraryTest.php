<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
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

    private function jpegWithPayload(string $payload): UploadedFile
    {
        $source = UploadedFile::fake()->image('source.jpg', 800, 600);
        $bytes = file_get_contents($source->getRealPath()).$payload;
        $path = tempnam(sys_get_temp_dir(), 'icsmi');
        file_put_contents($path, $bytes);

        return new UploadedFile($path, 'photo.jpg', 'image/jpeg', null, true);
    }

    private function pdfUpload(string $content = "%PDF-1.4\n%fake\n"): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('naskah.pdf', $content);
    }

    public function test_admin_can_upload_an_image_with_alt_text_and_resized_variants(): void
    {
        $response = $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('banner.jpg', 2400, 1200),
            'alt_text' => 'Peserta konferensi di ruang utama',
            'caption' => 'Foto dokumentasi 2026',
        ]);

        $response->assertRedirect(route('library.index'));
        $media = Media::sole();
        $this->assertSame(Media::KIND_IMAGE, $media->kind);
        $this->assertSame('Peserta konferensi di ruang utama', $media->alt_text);
        $this->assertSame(2400, $media->width);
        $this->assertArrayHasKey('desktop', $media->paths);
        $this->assertArrayHasKey('mobile', $media->paths);
        Storage::disk('local')->assertExists($media->paths['desktop']);
        Storage::disk('local')->assertExists($media->paths['mobile']);
    }

    public function test_image_without_alt_text_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('banner.jpg', 400, 300),
            'alt_text' => '',
        ]);

        $response->assertSessionHasErrors('alt_text');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_php_file_disguised_as_jpg_is_rejected(): void
    {
        $disguised = UploadedFile::fake()->createWithContent('foto.jpg', '<?php echo "x"; ?>');

        $response = $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => $disguised,
            'alt_text' => 'Teks alternatif',
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_svg_file_is_rejected(): void
    {
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $response = $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => $svg,
            'alt_text' => 'Logo',
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_payload_appended_to_an_image_is_removed_by_reencoding(): void
    {
        $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => $this->jpegWithPayload('<?php echo "tersembunyi"; ?>'),
            'alt_text' => 'Foto',
        ])->assertRedirect();

        $stored = Storage::disk('local')->get(Media::sole()->paths['desktop']);

        $this->assertStringNotContainsString('<?php', $stored);
        $this->assertNotFalse(getimagesizefromstring($stored));
    }

    public function test_image_over_five_megabytes_is_rejected(): void
    {
        $oversized = $this->jpegWithPayload(str_repeat('a', 5 * 1024 * 1024));

        $response = $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => $oversized,
            'alt_text' => 'Foto',
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_pdf_with_valid_header_is_stored_and_served_as_a_download(): void
    {
        $this->actingAs($this->editor)->post(route('library.store'), [
            'file' => $this->pdfUpload(),
            'caption' => 'Panduan penulis',
        ])->assertRedirect();

        $media = Media::sole();
        $this->assertSame(Media::KIND_PDF, $media->kind);
        $this->assertNull($media->alt_text);

        $response = $this->get(route('media.show', [$media, 'original']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_fake_pdf_without_header_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => $this->pdfUpload('<html><script>alert(1)</script></html>'),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('media', 0);
    }

    public function test_images_are_served_with_safe_headers_and_long_cache(): void
    {
        $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('foto.png', 300, 200),
            'alt_text' => 'Foto',
        ])->assertRedirect();
        $media = Media::sole();

        $response = $this->get(route('media.show', [$media, 'mobile']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('max-age=', $response->headers->get('Cache-Control'));
    }

    public function test_unknown_media_or_variant_returns_not_found(): void
    {
        $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('foto.jpg', 300, 200),
            'alt_text' => 'Foto',
        ])->assertRedirect();
        $media = Media::sole();

        $this->get('/media/00000000-0000-0000-0000-000000000000/desktop')->assertNotFound();
        $this->get(route('media.show', [$media, 'original']))->assertNotFound();
    }

    public function test_author_cannot_open_the_library(): void
    {
        $this->actingAs($this->author)->get(route('library.index'))->assertForbidden();
        $this->actingAs($this->author)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('foto.jpg', 300, 200),
            'alt_text' => 'Foto',
        ])->assertForbidden();
    }

    public function test_editor_can_upload_images(): void
    {
        $this->actingAs($this->editor)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('foto.jpg', 300, 200),
            'alt_text' => 'Foto',
        ])->assertRedirect(route('library.index'));

        $this->assertDatabaseCount('media', 1);
    }

    public function test_updating_alt_text_of_an_image_cannot_clear_it(): void
    {
        $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('foto.jpg', 300, 200),
            'alt_text' => 'Foto awal',
        ])->assertRedirect();
        $media = Media::sole();

        $response = $this->actingAs($this->admin)->put(route('library.update', $media), [
            'alt_text' => '',
            'caption' => 'Keterangan',
        ]);

        $response->assertSessionHasErrors('alt_text');
        $this->assertSame('Foto awal', $media->fresh()->alt_text);
    }

    public function test_deleting_media_removes_the_record_and_its_files(): void
    {
        $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('foto.jpg', 300, 200),
            'alt_text' => 'Foto',
        ])->assertRedirect();
        $media = Media::sole();
        $paths = array_values($media->paths);

        $this->actingAs($this->admin)->delete(route('library.destroy', $media))->assertRedirect();

        $this->assertDatabaseCount('media', 0);
        foreach ($paths as $path) {
            Storage::disk('local')->assertMissing($path);
        }
    }

    public function test_upload_and_delete_are_recorded_in_the_audit_log(): void
    {
        $this->actingAs($this->admin)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('foto.jpg', 300, 200),
            'alt_text' => 'Foto',
        ]);
        $media = Media::sole();

        $this->actingAs($this->admin)->delete(route('library.destroy', $media));

        $this->assertDatabaseHas('audit_logs', ['action' => 'media.uploaded', 'user_id' => $this->admin->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'media.deleted', 'user_id' => $this->admin->id]);
        $this->assertSame(2, AuditLog::where('action', 'like', 'media.%')->count());
    }
}
