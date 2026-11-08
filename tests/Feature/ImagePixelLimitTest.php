<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagePixelLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_above_the_pixel_limit_is_rejected_before_decoding(): void
    {
        Storage::fake('local');
        config(['media.max_image_pixels' => 1000]);
        $editor = User::factory()->create();
        $editor->assignRole(Role::Editor);

        $this->actingAs($editor)->post(route('library.store'), [
            'file' => UploadedFile::fake()->image('besar.jpg', 100, 100),
            'alt_text' => 'Gambar besar',
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, Media::count());
    }
}
