<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStatus;
use App\Enums\ReviewStatus;
use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\Manuscript;
use App\Models\ManuscriptFile;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Notifications\ManuscriptStatusChanged;
use App\Notifications\ManuscriptSubmitted;
use App\Notifications\ReviewInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use ZipArchive;

class ManuscriptWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    private User $editor;

    private User $reviewer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Notification::fake();

        $this->author = $this->userWith(Role::Author, ['name' => 'Hidden Author Name', 'email' => 'hidden.author@example.com']);
        $this->editor = $this->userWith(Role::Editor, ['name' => 'Eve Editor']);
        $this->reviewer = $this->userWith(Role::Reviewer, ['name' => 'Hidden Reviewer Name', 'email' => 'hidden.reviewer@example.com']);
    }

    private function userWith(Role $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    private function pdf(string $name = 'paper.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\ncontent\n%%EOF");
    }

    /**
     * @param  array<string, string>  $entries
     */
    private function zip(string $name, array $entries): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        foreach ($entries as $entry => $content) {
            $zip->addFromString($entry, $content);
        }
        $zip->close();

        return UploadedFile::fake()->createWithContent($name, file_get_contents($path));
    }

    private function submission(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'A Study of Green Supply Chains',
            'abstract' => 'An abstract.',
            'keywords' => 'green, supply chain',
            'authors' => [
                ['name' => 'Hidden Author Name', 'email' => 'hidden.author@example.com', 'affiliation' => 'Hidden University'],
                ['name' => '', 'email' => '', 'affiliation' => ''],
            ],
            'blinded_file' => $this->pdf(),
            'full_file' => $this->pdf('full-with-names.pdf'),
        ];
    }

    private function submit(array $overrides = []): Manuscript
    {
        $this->actingAs($this->author)
            ->post('/author/manuscripts', $this->submission($overrides))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        return Manuscript::latest('id')->firstOrFail();
    }

    private function moveTo(Manuscript $manuscript, ManuscriptStatus $status, ?string $note = null): void
    {
        $this->actingAs($this->editor)
            ->post('/editor/manuscripts/'.$manuscript->id.'/status', ['status' => $status->value, 'note' => $note])
            ->assertSessionHasNoErrors();
    }

    private function inReview(): Manuscript
    {
        $manuscript = $this->submit();
        $this->moveTo($manuscript, ManuscriptStatus::Screening);
        $this->moveTo($manuscript, ManuscriptStatus::InReview);

        return $manuscript->fresh();
    }

    private function assign(Manuscript $manuscript): ReviewAssignment
    {
        $this->actingAs($this->editor)
            ->post('/editor/manuscripts/'.$manuscript->id.'/reviewers', ['reviewer_id' => $this->reviewer->id, 'due_at' => now()->addWeek()->toDateString()])
            ->assertSessionHasNoErrors();

        return ReviewAssignment::latest('id')->firstOrFail();
    }

    private function accept(ReviewAssignment $assignment): void
    {
        $this->actingAs($this->reviewer)
            ->post('/reviewer/assignments/'.$assignment->id.'/respond', ['response' => 'accept'])
            ->assertSessionHasNoErrors();
    }

    private function completeReview(ReviewAssignment $assignment): void
    {
        $this->accept($assignment);

        $this->actingAs($this->reviewer)
            ->post('/reviewer/assignments/'.$assignment->id.'/review', [
                'recommendation' => 'minor_revision',
                'comments_for_author' => 'Comment shared with the author.',
                'comments_for_editor' => 'Confidential comment for editors.',
            ])
            ->assertSessionHasNoErrors();
    }

    private function fileOf(Manuscript $manuscript, string $kind, int $version = 1): ManuscriptFile
    {
        return ManuscriptFile::where(['manuscript_id' => $manuscript->id, 'kind' => $kind, 'version' => $version])->firstOrFail();
    }

    public function test_author_can_submit_a_manuscript(): void
    {
        $manuscript = $this->submit();

        $this->assertSame(ManuscriptStatus::Submitted, $manuscript->status);
        $this->assertSame($this->author->id, $manuscript->submitter_id);
        $this->assertSame(sprintf('ICSMI-%d-%04d', now()->year, $manuscript->id), $manuscript->code);
        $this->assertCount(1, $manuscript->authors);
        $this->assertTrue($manuscript->authors->first()->is_corresponding);
        $this->assertCount(2, $manuscript->files);
        $this->assertCount(1, $manuscript->histories);
        $this->assertDatabaseHas('audit_logs', ['action' => 'manuscript.submitted', 'user_id' => $this->author->id]);

        Notification::assertSentTo($this->editor, ManuscriptSubmitted::class);
    }

    public function test_uploads_get_random_names_outside_the_public_folder(): void
    {
        $manuscript = $this->submit(['blinded_file' => $this->pdf('../../evil name.pdf')]);
        $file = $this->fileOf($manuscript, ManuscriptFile::KIND_BLINDED);

        Storage::disk('local')->assertExists($file->path);
        $this->assertStringStartsWith('manuscripts/'.$manuscript->id.'/', $file->path);
        $this->assertStringNotContainsString('evil', $file->path);
        $this->assertStringNotContainsString('..', $file->original_name);
    }

    public function test_real_docx_is_accepted(): void
    {
        $docx = $this->zip('paper.docx', ['[Content_Types].xml' => '<Types/>', 'word/document.xml' => '<document/>']);

        $manuscript = $this->submit(['blinded_file' => $docx]);

        $this->assertSame('docx', $this->fileOf($manuscript, ManuscriptFile::KIND_BLINDED)->extension);
    }

    /**
     * @return array<string, array{callable(self): UploadedFile}>
     */
    public static function dangerousUploads(): array
    {
        return [
            'php named as pdf' => [fn (self $test) => UploadedFile::fake()->createWithContent('shell.pdf', '<?php system($_GET["c"]);')],
            'html named as pdf' => [fn (self $test) => UploadedFile::fake()->createWithContent('page.pdf', '<script>alert(1)</script>')],
            'php extension' => [fn (self $test) => UploadedFile::fake()->createWithContent('shell.php', "%PDF-1.4\n<?php phpinfo();")],
            'double extension' => [fn (self $test) => UploadedFile::fake()->createWithContent('shell.pdf.php', "%PDF-1.4\n")],
            'html extension' => [fn (self $test) => UploadedFile::fake()->createWithContent('page.html', "%PDF-1.4\n")],
            'plain zip named as docx' => [fn (self $test) => $test->zip('archive.docx', ['readme.txt' => 'hello'])],
            'macro docx' => [fn (self $test) => $test->zip('macro.docx', ['[Content_Types].xml' => '<Types/>', 'word/document.xml' => '<d/>', 'word/vbaProject.bin' => 'macro'])],
            'text named as docx' => [fn (self $test) => UploadedFile::fake()->createWithContent('fake.docx', 'not a zip')],
            'oversized pdf' => [fn (self $test) => UploadedFile::fake()->createWithContent('big.pdf', "%PDF-1.4\n")->size(20481)],
        ];
    }

    #[DataProvider('dangerousUploads')]
    public function test_dangerous_uploads_are_rejected(callable $upload): void
    {
        $this->actingAs($this->author)
            ->post('/author/manuscripts', $this->submission(['blinded_file' => $upload($this)]))
            ->assertSessionHasErrors('blinded_file');

        $this->assertDatabaseCount('manuscripts', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_only_authors_can_submit(): void
    {
        $this->actingAs($this->reviewer)->post('/author/manuscripts', $this->submission())->assertForbidden();
        $this->assertDatabaseCount('manuscripts', 0);
    }

    public function test_authors_cannot_reach_other_authors_manuscripts_by_changing_ids(): void
    {
        $manuscript = $this->submit();
        $other = $this->userWith(Role::Author);

        $this->actingAs($other)->get('/author/manuscripts/'.$manuscript->id)->assertForbidden();
        $this->actingAs($other)->get('/manuscript-files/'.$this->fileOf($manuscript, ManuscriptFile::KIND_BLINDED)->id)->assertForbidden();
        $this->actingAs($other)->get('/manuscript-files/'.$this->fileOf($manuscript, ManuscriptFile::KIND_FULL)->id)->assertForbidden();
        $this->actingAs($other)->post('/author/manuscripts/'.$manuscript->id.'/revision', ['blinded_file' => $this->pdf()])->assertForbidden();
        $this->actingAs($other)->get('/author')->assertDontSee($manuscript->title);
    }

    public function test_workflow_routes_reject_other_roles(): void
    {
        $manuscript = $this->submit();

        $this->actingAs($this->author)->get('/editor')->assertForbidden();
        $this->actingAs($this->author)->get('/editor/manuscripts/'.$manuscript->id)->assertForbidden();
        $this->actingAs($this->author)->post('/editor/manuscripts/'.$manuscript->id.'/status', ['status' => 'accepted'])->assertForbidden();
        $this->actingAs($this->reviewer)->post('/editor/manuscripts/'.$manuscript->id.'/status', ['status' => 'screening'])->assertForbidden();
        $this->actingAs($this->reviewer)->get('/editor/manuscripts/'.$manuscript->id)->assertForbidden();
        $this->assertSame(ManuscriptStatus::Submitted, $manuscript->fresh()->status);
    }

    public function test_reviewers_only_reach_their_own_assignments(): void
    {
        $assignment = $this->assign($this->inReview());
        $other = $this->userWith(Role::Reviewer);

        $this->actingAs($other)->get('/reviewer/assignments/'.$assignment->id)->assertForbidden();
        $this->actingAs($other)->post('/reviewer/assignments/'.$assignment->id.'/respond', ['response' => 'accept'])->assertForbidden();
        $this->actingAs($other)->post('/reviewer/assignments/'.$assignment->id.'/review', ['recommendation' => 'accept', 'comments_for_author' => 'x'])->assertForbidden();
        $this->actingAs($other)->get('/reviewer')->assertDontSee($assignment->manuscript->title);
        $this->assertSame(ReviewStatus::Invited, $assignment->fresh()->status);
    }

    public function test_reviewer_pages_and_mail_hide_author_identity(): void
    {
        $manuscript = $this->inReview();
        $assignment = $this->assign($manuscript);

        Notification::assertSentTo($this->reviewer, ReviewInvitation::class, function (ReviewInvitation $notification) {
            $mail = json_encode($notification->toMail($this->reviewer)->toArray());

            return ! str_contains($mail, 'Hidden Author') && ! str_contains($mail, 'hidden.author') && ! str_contains($mail, 'Hidden University');
        });

        $this->accept($assignment);

        foreach (['/reviewer', '/reviewer/assignments/'.$assignment->id] as $uri) {
            $html = $this->actingAs($this->reviewer)->get($uri)->assertOk()->getContent();

            $this->assertStringNotContainsString('Hidden Author Name', $html);
            $this->assertStringNotContainsString('hidden.author@example.com', $html);
            $this->assertStringNotContainsString('Hidden University', $html);
            $this->assertStringNotContainsString('full-with-names', $html);
        }
    }

    public function test_reviewer_file_access_follows_the_invitation_and_blinding(): void
    {
        $manuscript = $this->inReview();
        $assignment = $this->assign($manuscript);
        $blinded = $this->fileOf($manuscript, ManuscriptFile::KIND_BLINDED);
        $full = $this->fileOf($manuscript, ManuscriptFile::KIND_FULL);

        $this->actingAs($this->reviewer)->get('/manuscript-files/'.$blinded->id)->assertForbidden();

        $this->accept($assignment);

        $response = $this->actingAs($this->reviewer)->get('/manuscript-files/'.$blinded->id)->assertOk();
        $response->assertDownload($manuscript->code.'-v1-blinded.pdf');
        $this->assertStringNotContainsString('paper', (string) $response->headers->get('Content-Disposition'));

        $this->actingAs($this->reviewer)->get('/manuscript-files/'.$full->id)->assertForbidden();
    }

    public function test_declined_and_cancelled_reviewers_lose_file_access(): void
    {
        $manuscript = $this->inReview();
        $blinded = $this->fileOf($manuscript, ManuscriptFile::KIND_BLINDED);

        $declined = $this->assign($manuscript);
        $this->actingAs($this->reviewer)->post('/reviewer/assignments/'.$declined->id.'/respond', ['response' => 'decline']);
        $this->actingAs($this->reviewer)->get('/manuscript-files/'.$blinded->id)->assertForbidden();

        $second = $this->userWith(Role::Reviewer);
        $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/reviewers', ['reviewer_id' => $second->id, 'due_at' => now()->addWeek()->toDateString()]);
        $cancelled = ReviewAssignment::latest('id')->first();
        $this->actingAs($second)->post('/reviewer/assignments/'.$cancelled->id.'/respond', ['response' => 'accept']);
        $this->actingAs($this->editor)->post('/editor/assignments/'.$cancelled->id.'/cancel');

        $this->actingAs($second)->get('/manuscript-files/'.$blinded->id)->assertForbidden();
        $this->actingAs($second)->get('/reviewer/assignments/'.$cancelled->id)->assertForbidden();
    }

    public function test_author_pages_and_mail_hide_reviewer_identity_and_confidential_comments(): void
    {
        $manuscript = $this->inReview();
        $this->completeReview($this->assign($manuscript));
        $this->moveTo($manuscript, ManuscriptStatus::RevisionRequired, 'Please revise.');

        $html = $this->actingAs($this->author)->get('/author/manuscripts/'.$manuscript->id)->assertOk()->getContent();

        $this->assertStringContainsString('Comment shared with the author.', $html);
        $this->assertStringContainsString('Please revise.', $html);
        $this->assertStringNotContainsString('Hidden Reviewer Name', $html);
        $this->assertStringNotContainsString('hidden.reviewer@example.com', $html);
        $this->assertStringNotContainsString('Confidential comment', $html);
        $this->assertStringNotContainsString('Eve Editor', $html);

        Notification::assertSentTo($this->author, ManuscriptStatusChanged::class, function (ManuscriptStatusChanged $notification) {
            return ! str_contains(json_encode($notification->toMail($this->author)->toArray()), 'Hidden Reviewer');
        });
    }

    public function test_review_comments_stay_hidden_from_the_author_until_a_decision(): void
    {
        $manuscript = $this->inReview();
        $this->completeReview($this->assign($manuscript));

        $this->actingAs($this->author)->get('/author/manuscripts/'.$manuscript->id)
            ->assertOk()
            ->assertDontSee('Comment shared with the author.');
    }

    public function test_editor_sees_authors_reviewers_and_confidential_comments(): void
    {
        $manuscript = $this->inReview();
        $this->completeReview($this->assign($manuscript));

        $this->actingAs($this->editor)->get('/editor/manuscripts/'.$manuscript->id)
            ->assertOk()
            ->assertSee('Hidden Author Name')
            ->assertSee('Hidden Reviewer Name')
            ->assertSee('Confidential comment for editors.');
    }

    public function test_invalid_transitions_are_rejected(): void
    {
        $manuscript = $this->submit();

        foreach (['accepted', 'in_review', 'published', 'revision_required', 'submitted'] as $status) {
            $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/status', ['status' => $status])->assertSessionHasErrors('status');
        }

        $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/status', ['status' => "x' OR 1=1 --"])->assertSessionHasErrors('status');

        $this->assertSame(ManuscriptStatus::Submitted, $manuscript->fresh()->status);
        $this->assertCount(1, $manuscript->histories);
    }

    public function test_acceptance_requires_a_completed_review(): void
    {
        $manuscript = $this->inReview();

        $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/status', ['status' => 'accepted'])->assertSessionHasErrors('status');
        $this->assertSame(ManuscriptStatus::InReview, $manuscript->fresh()->status);

        $this->completeReview($this->assign($manuscript));
        $this->moveTo($manuscript, ManuscriptStatus::Accepted, 'Congratulations.');

        $fresh = $manuscript->fresh();
        $this->assertSame(ManuscriptStatus::Accepted, $fresh->status);
        $this->assertNotNull($fresh->decided_at);
    }

    public function test_each_status_change_is_recorded_and_notified(): void
    {
        $manuscript = $this->inReview();

        $this->assertSame(
            ['in_review', 'screening', 'submitted'],
            $manuscript->histories()->get()->map(fn ($history) => $history->to_status->value)->all(),
        );
        $this->assertSame($this->editor->id, $manuscript->histories()->first()->actor_id);
        $this->assertSame(2, AuditLog::where('action', 'manuscript.status_changed')->count());

        Notification::assertSentToTimes($this->author, ManuscriptStatusChanged::class, 2);
    }

    public function test_revision_creates_a_new_version_and_review_round(): void
    {
        $manuscript = $this->inReview();
        $first = $this->assign($manuscript);
        $this->completeReview($first);
        $this->moveTo($manuscript, ManuscriptStatus::RevisionRequired);

        $this->actingAs($this->author)
            ->post('/author/manuscripts/'.$manuscript->id.'/revision', ['blinded_file' => $this->pdf('v2.pdf'), 'note' => 'Updated section 2.'])
            ->assertSessionHasNoErrors();

        $fresh = $manuscript->fresh();
        $this->assertSame(2, $fresh->current_version);
        $this->assertSame(ManuscriptStatus::Screening, $fresh->status);
        Storage::disk('local')->assertExists($this->fileOf($manuscript, ManuscriptFile::KIND_BLINDED, 1)->path);
        Storage::disk('local')->assertExists($this->fileOf($manuscript, ManuscriptFile::KIND_BLINDED, 2)->path);

        $v2 = $this->fileOf($manuscript, ManuscriptFile::KIND_BLINDED, 2);
        $this->actingAs($this->reviewer)->get('/manuscript-files/'.$v2->id)->assertForbidden();

        $this->moveTo($manuscript, ManuscriptStatus::InReview);
        $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/status', ['status' => 'accepted'])->assertSessionHasErrors('status');

        $second = $this->assign($manuscript->fresh());
        $this->assertSame(2, $second->round);
    }

    public function test_revision_is_only_possible_when_requested(): void
    {
        $manuscript = $this->submit();

        $this->actingAs($this->author)
            ->post('/author/manuscripts/'.$manuscript->id.'/revision', ['blinded_file' => $this->pdf()])
            ->assertForbidden();

        $this->assertSame(1, $manuscript->fresh()->current_version);
    }

    public function test_review_cannot_be_submitted_twice_or_before_accepting(): void
    {
        $assignment = $this->assign($this->inReview());
        $payload = ['recommendation' => 'accept', 'comments_for_author' => 'Second attempt'];

        $this->actingAs($this->reviewer)->post('/reviewer/assignments/'.$assignment->id.'/review', $payload)->assertForbidden();

        $this->completeReview($assignment);

        $this->actingAs($this->reviewer)->post('/reviewer/assignments/'.$assignment->id.'/review', $payload)->assertForbidden();
        $this->actingAs($this->reviewer)->post('/reviewer/assignments/'.$assignment->id.'/respond', ['response' => 'decline'])->assertForbidden();
        $this->assertSame('Comment shared with the author.', $assignment->fresh()->comments_for_author);
    }

    public function test_review_rejects_invalid_recommendations(): void
    {
        $assignment = $this->assign($this->inReview());
        $this->accept($assignment);

        $this->actingAs($this->reviewer)
            ->post('/reviewer/assignments/'.$assignment->id.'/review', ['recommendation' => 'approve-all', 'comments_for_author' => 'x'])
            ->assertSessionHasErrors('recommendation');
    }

    public function test_editors_cannot_handle_their_own_manuscripts(): void
    {
        $editorAuthor = $this->userWith(Role::Editor, ['email' => 'Editor.Author@example.com']);
        $manuscript = $this->submit([
            'title' => 'Editor Co-authored Paper',
            'authors' => [
                ['name' => 'Hidden Author Name', 'email' => 'hidden.author@example.com', 'affiliation' => 'Hidden University'],
                ['name' => 'Editor Author', 'email' => 'editor.author@EXAMPLE.com', 'affiliation' => 'Hidden University'],
            ],
        ]);

        $this->actingAs($editorAuthor)->get('/editor')->assertOk()->assertDontSee('Editor Co-authored Paper');
        $this->actingAs($editorAuthor)->get('/editor/manuscripts/'.$manuscript->id)->assertForbidden();
        $this->actingAs($editorAuthor)->post('/editor/manuscripts/'.$manuscript->id.'/status', ['status' => 'screening'])->assertForbidden();
        $this->actingAs($editorAuthor)->get('/manuscript-files/'.$this->fileOf($manuscript, ManuscriptFile::KIND_FULL)->id)->assertForbidden();

        Notification::assertNotSentTo($editorAuthor, ManuscriptSubmitted::class);
    }

    public function test_authors_cannot_be_assigned_as_reviewers(): void
    {
        $coAuthorReviewer = $this->userWith(Role::Reviewer, ['email' => 'Co.Author@example.com']);
        $manuscript = $this->submit(['authors' => [
            ['name' => 'Hidden Author Name', 'email' => 'hidden.author@example.com', 'affiliation' => 'Hidden University'],
            ['name' => 'Co Author', 'email' => 'co.author@example.com', 'affiliation' => 'Hidden University'],
        ]]);
        $this->moveTo($manuscript, ManuscriptStatus::Screening);
        $this->moveTo($manuscript, ManuscriptStatus::InReview);

        foreach ([$coAuthorReviewer, $this->author, $this->editor] as $user) {
            $this->actingAs($this->editor)
                ->post('/editor/manuscripts/'.$manuscript->id.'/reviewers', ['reviewer_id' => $user->id, 'due_at' => now()->addWeek()->toDateString()])
                ->assertSessionHasErrors('reviewer_id');
        }

        $this->assertDatabaseCount('review_assignments', 0);
    }

    public function test_reviewers_can_only_be_assigned_during_review_and_once_per_round(): void
    {
        $manuscript = $this->submit();
        $payload = ['reviewer_id' => $this->reviewer->id, 'due_at' => now()->addWeek()->toDateString()];

        $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/reviewers', $payload)->assertSessionHasErrors('reviewer_id');

        $this->moveTo($manuscript, ManuscriptStatus::Screening);
        $this->moveTo($manuscript, ManuscriptStatus::InReview);
        $this->assign($manuscript);

        $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/reviewers', $payload)->assertSessionHasErrors('reviewer_id');
        $this->assertDatabaseCount('review_assignments', 1);
    }

    public function test_workflow_forms_require_csrf_tokens(): void
    {
        $manuscript = $this->inReview();
        $assignment = $this->assign($manuscript);

        $this->app['env'] = 'local';

        $requests = [
            [$this->author, '/author/manuscripts'],
            [$this->author, '/author/manuscripts/'.$manuscript->id.'/revision'],
            [$this->reviewer, '/reviewer/assignments/'.$assignment->id.'/respond'],
            [$this->reviewer, '/reviewer/assignments/'.$assignment->id.'/review'],
            [$this->editor, '/editor/manuscripts/'.$manuscript->id.'/status'],
            [$this->editor, '/editor/manuscripts/'.$manuscript->id.'/reviewers'],
            [$this->editor, '/editor/assignments/'.$assignment->id.'/cancel'],
        ];

        foreach ($requests as [$user, $uri]) {
            $this->actingAs($user)->withSession(['_token' => 'expected-token'])->post($uri, [])->assertStatus(419);
        }
    }

    public function test_manuscript_text_is_escaped_on_every_workflow_page(): void
    {
        $payload = '"><script>alert(1)</script>';
        $manuscript = $this->submit(['title' => $payload, 'abstract' => $payload, 'keywords' => $payload]);
        $this->moveTo($manuscript, ManuscriptStatus::Screening, $payload);
        $this->moveTo($manuscript, ManuscriptStatus::InReview);
        $assignment = $this->assign($manuscript);
        $this->completeReview($assignment);

        $pages = [
            [$this->author, '/author'],
            [$this->author, '/author/manuscripts/'.$manuscript->id],
            [$this->editor, '/editor'],
            [$this->editor, '/editor/manuscripts/'.$manuscript->id],
            [$this->reviewer, '/reviewer'],
            [$this->reviewer, '/reviewer/assignments/'.$assignment->id],
        ];

        foreach ($pages as [$user, $uri]) {
            $html = $this->actingAs($user)->get($uri)->assertOk()->getContent();

            $this->assertStringNotContainsString('<script>alert(1)', $html, $uri);
        }
    }

    public function test_status_filter_only_accepts_known_statuses(): void
    {
        $this->actingAs($this->editor)->get('/editor?'.http_build_query(['status' => "x' OR '1'='1"]))->assertSessionHasErrors('status');
        $this->actingAs($this->editor)->get('/editor?status=submitted')->assertOk();
    }

    public function test_workflow_routes_require_verified_email(): void
    {
        $unverified = User::factory()->unverified()->create();
        $unverified->assignRole(Role::Author);

        $this->actingAs($unverified)->post('/author/manuscripts', $this->submission())->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('manuscripts', 0);
    }
}
