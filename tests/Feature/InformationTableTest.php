<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\HomeBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformationTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_line_becomes_an_aligned_label_and_value_row(): void
    {
        HomeBlock::create([
            'type' => HomeBlock::TYPE_TABLE,
            'sort_order' => 0,
            'is_visible' => true,
            'heading' => 'About the Journal',
            'body' => "Journal Title | International Conference on Sustainable Management and Innovation (ICSMI)\nFrequency | June, December\nCitation Analysis | -",
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-block="table"', $html);
        $this->assertStringContainsString('About the Journal', $html);
        $this->assertStringContainsString('<th scope="row" class="w-48 py-2 pe-4 text-left font-semibold text-gray-900">Frequency</th>', $html);
        $this->assertStringContainsString('June, December', $html);
    }

    public function test_table_values_are_escaped_on_the_public_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::Admin);

        $this->actingAs($admin)->post(route('admin.home-blocks.store'), [
            'type' => HomeBlock::TYPE_TABLE,
            'sort_order' => 0,
            'is_visible' => '1',
            'heading' => 'Info',
            'body' => 'Publisher | <script>alert(1)</script>',
        ])->assertSessionHasNoErrors();

        $this->get('/')->assertDontSee('<script>alert(1)', false);
    }

    public function test_an_uploaded_cover_image_shows_beside_the_table(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::Admin);
        \Illuminate\Support\Facades\Storage::fake('local');

        $this->actingAs($admin)->post(route('admin.home-blocks.store'), [
            'type' => HomeBlock::TYPE_TABLE,
            'sort_order' => 0,
            'is_visible' => '1',
            'heading' => 'About the Journal',
            'body' => 'Frequency | June, December',
            'cover_image' => \Illuminate\Http\UploadedFile::fake()->image('sampul.jpg', 600, 800),
            'cover_alt' => 'Sampul prosiding ICSMI',
        ])->assertSessionHasNoErrors();

        $media = \App\Models\Media::sole();
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-cover-image', $html);
        $this->assertStringContainsString('/media/'.$media->uuid.'/desktop', $html);
        $this->assertStringContainsString('Sampul prosiding ICSMI', $html);
    }

    public function test_a_table_without_a_cover_shows_only_the_table(): void
    {
        HomeBlock::create([
            'type' => HomeBlock::TYPE_TABLE,
            'sort_order' => 0,
            'is_visible' => true,
            'heading' => 'About the Journal',
            'body' => 'Frequency | June, December',
        ]);

        $this->get('/')->assertOk()->assertDontSee('data-cover-image', false);
    }

    public function test_a_table_needs_its_body(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::Admin);

        $this->actingAs($admin)->post(route('admin.home-blocks.store'), [
            'type' => HomeBlock::TYPE_TABLE,
            'sort_order' => 0,
            'is_visible' => '1',
            'heading' => 'Info',
            'body' => '',
        ])->assertSessionHasErrors('body');
    }
}
