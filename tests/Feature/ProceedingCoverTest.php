<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Issue;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProceedingCoverTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->editor = User::factory()->create();
        $this->editor->assignRole(Role::Editor);
    }

    private function coverMedia(): Media
    {
        return app(MediaUploader::class)->store(
            UploadedFile::fake()->image('sampul.jpg', 800, 1000),
            'Sampul proceedings',
            null,
            $this->editor,
        );
    }

    public function test_archive_shows_cover_image_beside_each_proceeding(): void
    {
        $cover = $this->coverMedia();
        Issue::factory()->create(['title' => 'Berber Proceedings', 'cover_media_id' => $cover->id]);

        $this->get('/archives')
            ->assertOk()
            ->assertSee('data-cover-image', false)
            ->assertSee(route('media.show', [$cover, 'desktop']), false);
    }

    public function test_archive_renders_without_cover_when_proceeding_has_none(): void
    {
        Issue::factory()->create(['title' => 'Tanpa Sampul']);

        $this->get('/archives')
            ->assertOk()
            ->assertSee('Tanpa Sampul')
            ->assertDontSee('data-cover-image', false);
    }

    public function test_editor_can_upload_a_cover_when_updating_a_proceeding(): void
    {
        $issue = Issue::factory()->create(['title' => 'Edit Sampul']);

        $this->actingAs($this->editor)->put(route('editor.issues.update', $issue), [
            'title' => 'Edit Sampul',
            'year' => $issue->year,
            'published' => '1',
            'cover_image' => UploadedFile::fake()->image('sampul.jpg', 800, 1000),
            'cover_alt' => 'Sampul proceedings 2026',
        ])->assertRedirect(route('editor.issues.index'));

        $issue->refresh();
        $this->assertNotNull($issue->cover_media_id);
        $this->assertSame('Sampul proceedings 2026', $issue->coverMedia->alt_text);
    }

    public function test_cover_must_reference_an_image_not_a_pdf(): void
    {
        $pdf = app(MediaUploader::class)->store(
            UploadedFile::fake()->createWithContent('naskah.pdf', "%PDF-1.4\n%fake\n"),
            null,
            null,
            $this->editor,
        );
        $issue = Issue::factory()->create(['title' => 'Bukan Gambar']);

        $this->actingAs($this->editor)->put(route('editor.issues.update', $issue), [
            'title' => 'Bukan Gambar',
            'year' => $issue->year,
            'cover_media_id' => $pdf->id,
        ])->assertSessionHasErrors('cover_media_id');
    }
}
