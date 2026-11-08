<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStatus;
use App\Enums\Role;
use App\Models\Manuscript;
use App\Models\ManuscriptFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorWorkspaceLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function author(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::Author);

        return $user;
    }

    public function test_author_list_uses_new_submission_action(): void
    {
        $this->actingAs($this->author())
            ->get(route('author.dashboard'))
            ->assertOk()
            ->assertSee('New Submission')
            ->assertDontSee('Submit Manuscript');
    }

    public function test_author_detail_shows_identification_with_authors_and_files_table(): void
    {
        $author = $this->author();
        $manuscript = Manuscript::create([
            'code' => 'ICSMI-TEST-1',
            'submitter_id' => $author->id,
            'title' => 'Naskah Uji',
            'abstract' => 'Abstrak.',
            'keywords' => 'uji',
            'status' => ManuscriptStatus::Submitted,
            'current_version' => 1,
            'submitted_at' => now(),
        ]);
        Model::unguarded(fn () => $manuscript->authors()->create([
            'name' => 'Ada Lovelace', 'email' => 'ada@example.test', 'affiliation' => 'Uji', 'position' => 0, 'is_corresponding' => true,
        ]));
        Model::unguarded(fn () => ManuscriptFile::create([
            'manuscript_id' => $manuscript->id,
            'uploaded_by' => $author->id,
            'version' => 1,
            'kind' => ManuscriptFile::KIND_BLINDED,
            'path' => 'manuscripts/1/a.pdf',
            'original_name' => 'naskah.pdf',
            'extension' => 'pdf',
            'size' => 2048,
            'sha256' => str_repeat('b', 64),
        ]));

        $this->actingAs($author)
            ->get(route('author.manuscripts.show', $manuscript))
            ->assertOk()
            ->assertSee('data-files-table', false)
            ->assertSee('Component')
            ->assertSee('Ada Lovelace')
            ->assertSee('Naskah Uji');
    }
}
