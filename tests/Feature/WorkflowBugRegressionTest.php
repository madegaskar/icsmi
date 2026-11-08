<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStatus;
use App\Enums\ReviewStatus;
use App\Enums\Role;
use App\Models\Manuscript;
use App\Models\ManuscriptFile;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Services\ManuscriptWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WorkflowBugRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(Role ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->assignRole($role);
        }

        return $user;
    }

    private function manuscript(User $submitter, ManuscriptStatus $status, int $version = 1): Manuscript
    {
        return Manuscript::create([
            'code' => 'ICSMI-TEST-'.uniqid(),
            'submitter_id' => $submitter->id,
            'title' => 'Naskah uji',
            'abstract' => 'Abstrak uji.',
            'keywords' => 'uji',
            'status' => $status,
            'current_version' => $version,
            'submitted_at' => now(),
        ]);
    }

    private function assignment(Manuscript $manuscript, User $reviewer, ReviewStatus $status): ReviewAssignment
    {
        return Model::unguarded(fn () => ReviewAssignment::create([
            'manuscript_id' => $manuscript->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => $manuscript->submitter_id,
            'round' => $manuscript->current_version,
            'status' => $status,
            'due_at' => now()->addWeeks(2),
        ]));
    }

    public function test_reviewer_with_accepted_assignment_can_open_page_before_blinded_file_exists(): void
    {
        $author = $this->userWith(Role::Author);
        $reviewer = $this->userWith(Role::Reviewer);
        $manuscript = $this->manuscript($author, ManuscriptStatus::InReview);
        $assignment = $this->assignment($manuscript, $reviewer, ReviewStatus::Accepted);

        $this->actingAs($reviewer)
            ->get(route('reviewer.assignments.show', $assignment))
            ->assertOk();
    }

    public function test_reviewer_who_lost_reviewer_role_cannot_download_blinded_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('manuscripts/1/a.pdf', "%PDF-1.4\n");
        $author = $this->userWith(Role::Author);
        $formerReviewer = $this->userWith(Role::Author);
        $manuscript = $this->manuscript($author, ManuscriptStatus::InReview);
        $this->assignment($manuscript, $formerReviewer, ReviewStatus::Accepted);
        $file = Model::unguarded(fn () => ManuscriptFile::create([
            'manuscript_id' => $manuscript->id,
            'uploaded_by' => $author->id,
            'version' => 1,
            'kind' => ManuscriptFile::KIND_BLINDED,
            'path' => 'manuscripts/1/a.pdf',
            'original_name' => 'a.pdf',
            'extension' => 'pdf',
            'size' => 9,
            'sha256' => str_repeat('a', 64),
        ]));

        $this->actingAs($formerReviewer)
            ->get(route('manuscript-files.download', $file))
            ->assertForbidden();
    }

    public function test_revision_is_rejected_when_manuscript_is_not_awaiting_revision(): void
    {
        Storage::fake('local');
        $author = $this->userWith(Role::Author);
        $manuscript = $this->manuscript($author, ManuscriptStatus::Screening, 2);
        $blinded = UploadedFile::fake()->createWithContent('baru.pdf', "%PDF-1.4\n");

        try {
            app(ManuscriptWorkflow::class)->submitRevision($manuscript, $author, $blinded, null, null);
            $this->fail('Expected a validation error.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame(2, $manuscript->fresh()->current_version);
    }
}
