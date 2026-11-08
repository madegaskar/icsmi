<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Article;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticlePdfReplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_replacing_an_article_pdf_deletes_the_previous_file(): void
    {
        Storage::fake('local');
        $editor = User::factory()->create();
        $editor->assignRole(Role::Editor);
        $issue = Issue::factory()->create();
        Storage::disk('local')->put('articles/old.pdf', "%PDF-1.4\nold");
        $article = Article::factory()->create(['issue_id' => $issue->id, 'pdf_path' => 'articles/old.pdf']);

        $this->actingAs($editor)->put(route('editor.articles.update', $article), [
            'issue_id' => $issue->id,
            'title' => $article->title,
            'abstract' => 'Abstrak baru.',
            'authors' => [['name' => 'Ada Lovelace']],
            'pdf' => UploadedFile::fake()->createWithContent('baru.pdf', "%PDF-1.4\nnew"),
        ])->assertRedirect();

        $article->refresh();
        $this->assertNotSame('articles/old.pdf', $article->pdf_path);
        Storage::disk('local')->assertExists($article->pdf_path);
        Storage::disk('local')->assertMissing('articles/old.pdf');
    }
}
