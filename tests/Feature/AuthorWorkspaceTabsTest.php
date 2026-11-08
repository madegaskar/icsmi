<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStatus;
use App\Enums\Role;
use App\Models\Manuscript;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorWorkspaceTabsTest extends TestCase
{
    use RefreshDatabase;

    private function manuscript(User $author, string $title, ManuscriptStatus $status): void
    {
        Manuscript::create([
            'code' => 'ICSMI-'.uniqid(),
            'submitter_id' => $author->id,
            'title' => $title,
            'abstract' => 'Abstrak.',
            'keywords' => 'uji',
            'status' => $status,
            'current_version' => 1,
            'submitted_at' => now(),
        ]);
    }

    private function author(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::Author);

        return $user;
    }

    public function test_my_queue_lists_open_manuscripts_and_hides_archived_ones(): void
    {
        $author = $this->author();
        $this->manuscript($author, 'Naskah Sedang Ditinjau', ManuscriptStatus::Screening);
        $this->manuscript($author, 'Naskah Sudah Terbit', ManuscriptStatus::Published);

        $this->actingAs($author)
            ->get(route('author.dashboard'))
            ->assertOk()
            ->assertSee('Naskah Sedang Ditinjau')
            ->assertDontSee('Naskah Sudah Terbit');
    }

    public function test_archives_tab_lists_only_finished_manuscripts(): void
    {
        $author = $this->author();
        $this->manuscript($author, 'Naskah Sedang Ditinjau', ManuscriptStatus::Screening);
        $this->manuscript($author, 'Naskah Sudah Terbit', ManuscriptStatus::Published);

        $this->actingAs($author)
            ->get(route('author.dashboard', ['tab' => 'archive']))
            ->assertOk()
            ->assertSee('Naskah Sudah Terbit')
            ->assertDontSee('Naskah Sedang Ditinjau');
    }

    public function test_search_filters_the_list_by_title_or_code(): void
    {
        $author = $this->author();
        $this->manuscript($author, 'Supply Chain Hijau', ManuscriptStatus::Submitted);
        $this->manuscript($author, 'Literasi Keuangan', ManuscriptStatus::Submitted);

        $this->actingAs($author)
            ->get(route('author.dashboard', ['q' => 'Literasi']))
            ->assertOk()
            ->assertSee('Literasi Keuangan')
            ->assertDontSee('Supply Chain Hijau');
    }

    public function test_workspace_has_tabs_search_and_new_submission(): void
    {
        $this->actingAs($this->author())
            ->get(route('author.dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Workspace tabs"', false)
            ->assertSee('My Queue')
            ->assertSee('Archives')
            ->assertSee('name="q"', false)
            ->assertSee('New Submission')
            ->assertDontSee('Submit Manuscript');
    }
}
