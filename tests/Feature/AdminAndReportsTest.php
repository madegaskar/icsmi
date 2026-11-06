<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStatus;
use App\Enums\ReviewStatus;
use App\Enums\Role;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\ArticleStat;
use App\Models\AuditLog;
use App\Models\BoardMember;
use App\Models\Issue;
use App\Models\Manuscript;
use App\Models\Page;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Notifications\ManuscriptStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use ZipArchive;

class AdminAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $editor;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Notification::fake();

        $this->admin = $this->userWith(Role::Admin);
        $this->editor = $this->userWith(Role::Editor);
        $this->author = $this->userWith(Role::Author);
    }

    private function userWith(Role $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    private function manuscript(ManuscriptStatus $status = ManuscriptStatus::Accepted, array $attributes = []): Manuscript
    {
        $manuscript = Manuscript::create($attributes + [
            'code' => 'ICSMI-TEST-'.fake()->unique()->numberBetween(1000, 9999),
            'submitter_id' => $this->author->id,
            'title' => 'Accepted Paper on Innovation',
            'abstract' => 'Accepted abstract.',
            'keywords' => 'innovation, management',
            'status' => $status,
            'current_version' => 1,
            'submitted_at' => now()->subDays(10),
        ]);

        $manuscript->authors()->create(['name' => 'Real Author', 'email' => 'real.author@example.com', 'affiliation' => 'Real University', 'is_corresponding' => true]);

        return $manuscript;
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('final.pdf', "%PDF-1.4\nfinal\n%%EOF");
    }

    public function test_accepted_manuscript_becomes_a_public_article(): void
    {
        $manuscript = $this->manuscript();
        $issue = Issue::factory()->create();

        $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/publish', [
            'issue_id' => $issue->id,
            'doi' => '10.1234/icsmi.2026.1',
            'first_page' => 5,
            'last_page' => 12,
            'pdf' => $this->pdf(),
        ])->assertSessionHasNoErrors();

        $manuscript->refresh();
        $article = $manuscript->article;

        $this->assertSame(ManuscriptStatus::Published, $manuscript->status);
        $this->assertSame('Accepted Paper on Innovation', $article->title);
        $this->assertSame(['innovation', 'management'], $article->keywords->pluck('name')->sort()->values()->all());
        $this->assertSame('Real Author', $article->authors->first()->name);
        Storage::disk('local')->assertExists($article->pdf_path);
        $this->assertStringNotContainsString('final', $article->pdf_path);

        $this->get('/articles/'.$article->slug)->assertOk()->assertSee('Real Author')->assertDontSee('real.author@example.com');
        $this->get('/articles/'.$article->slug.'/pdf')->assertOk();

        $this->assertSame(ManuscriptStatus::Published, $manuscript->histories()->first()->to_status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'manuscript.published', 'user_id' => $this->editor->id]);
        Notification::assertSentTo($this->author, ManuscriptStatusChanged::class);
    }

    public function test_article_in_unpublished_proceedings_stays_hidden(): void
    {
        $manuscript = $this->manuscript();
        $issue = Issue::factory()->unpublished()->create();

        $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/publish', ['issue_id' => $issue->id, 'pdf' => $this->pdf()])
            ->assertSessionHasNoErrors();

        $slug = $manuscript->fresh()->article->slug;

        $this->get('/articles/'.$slug)->assertNotFound();

        $this->actingAs($this->editor)->put('/editor/issues/'.$issue->id, ['title' => $issue->title, 'year' => $issue->year, 'published' => '1'])
            ->assertSessionHasNoErrors();

        $this->get('/articles/'.$slug)->assertOk();
    }

    /**
     * @return array<string, array{ManuscriptStatus}>
     */
    public static function unpublishableStatuses(): array
    {
        return [
            'submitted' => [ManuscriptStatus::Submitted],
            'in review' => [ManuscriptStatus::InReview],
            'rejected' => [ManuscriptStatus::Rejected],
            'published' => [ManuscriptStatus::Published],
        ];
    }

    #[DataProvider('unpublishableStatuses')]
    public function test_only_accepted_manuscripts_can_be_published(ManuscriptStatus $status): void
    {
        $manuscript = $this->manuscript($status);

        $this->actingAs($this->editor)
            ->post('/editor/manuscripts/'.$manuscript->id.'/publish', ['issue_id' => Issue::factory()->create()->id, 'pdf' => $this->pdf()])
            ->assertSessionHasErrors('publish');

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_publishing_validates_the_file_doi_pages_and_issue(): void
    {
        $manuscript = $this->manuscript();
        $issue = Issue::factory()->create();
        $uri = '/editor/manuscripts/'.$manuscript->id.'/publish';

        $this->actingAs($this->editor)->post($uri, ['issue_id' => $issue->id, 'pdf' => UploadedFile::fake()->createWithContent('x.pdf', '<?php phpinfo();')])->assertSessionHasErrors('pdf');
        $this->actingAs($this->editor)->post($uri, ['issue_id' => $issue->id, 'pdf' => $this->docx()])->assertSessionHasErrors('pdf');
        $this->actingAs($this->editor)->post($uri, ['issue_id' => $issue->id, 'pdf' => $this->pdf(), 'doi' => 'javascript:alert(1)'])->assertSessionHasErrors('doi');
        $this->actingAs($this->editor)->post($uri, ['issue_id' => $issue->id, 'pdf' => $this->pdf(), 'first_page' => 9, 'last_page' => 3])->assertSessionHasErrors('last_page');
        $this->actingAs($this->editor)->post($uri, ['issue_id' => 99999, 'pdf' => $this->pdf()])->assertSessionHasErrors('issue_id');

        $this->assertDatabaseCount('articles', 0);
    }

    private function docx(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<Types/>');
        $zip->addFromString('word/document.xml', '<d/>');
        $zip->close();

        return UploadedFile::fake()->createWithContent('final.docx', file_get_contents($path));
    }

    public function test_editor_who_wrote_a_manuscript_cannot_publish_it(): void
    {
        $manuscript = $this->manuscript(ManuscriptStatus::Accepted, ['submitter_id' => $this->editor->id]);

        $this->actingAs($this->editor)
            ->post('/editor/manuscripts/'.$manuscript->id.'/publish', ['issue_id' => Issue::factory()->create()->id, 'pdf' => $this->pdf()])
            ->assertForbidden();
    }

    public function test_editor_can_manage_proceedings_and_articles(): void
    {
        $this->actingAs($this->editor)->post('/editor/issues', ['title' => 'Proceedings of ICSMI 2026', 'year' => 2026, 'published' => '1'])->assertSessionHasNoErrors();
        $this->actingAs($this->editor)->post('/editor/issues', ['title' => 'Proceedings of ICSMI 2026', 'year' => 2026])->assertSessionHasNoErrors();

        $issues = Issue::orderBy('id')->get();
        $this->assertSame('proceedings-of-icsmi-2026', $issues[0]->slug);
        $this->assertSame('proceedings-of-icsmi-2026-2', $issues[1]->slug);
        $this->assertNotNull($issues[0]->published_at);
        $this->assertNull($issues[1]->published_at);

        $this->actingAs($this->editor)->post('/editor/articles', [
            'issue_id' => $issues[0]->id,
            'title' => 'Older Article',
            'abstract' => 'Abstract',
            'keywords' => 'a, b',
            'published' => '1',
            'pdf' => $this->pdf(),
            'authors' => [['name' => 'First Author', 'affiliation' => 'U'], ['name' => '', 'affiliation' => '']],
        ])->assertSessionHasNoErrors();

        $article = Article::firstOrFail();
        $this->assertNull($article->manuscript_id);
        $this->assertCount(1, $article->authors);

        $this->actingAs($this->editor)->put('/editor/articles/'.$article->id, [
            'issue_id' => $issues[0]->id,
            'title' => 'Renamed Article',
            'abstract' => 'Abstract',
            'authors' => [['name' => 'New Author']],
        ])->assertSessionHasNoErrors();

        $article->refresh();
        $this->assertSame('Renamed Article', $article->title);
        $this->assertNull($article->published_at);
        $this->assertSame(['New Author'], $article->authors->pluck('name')->all());
        $this->assertNotNull($article->pdf_path);
    }

    public function test_issue_identifiers_are_validated(): void
    {
        $this->actingAs($this->editor)
            ->post('/editor/issues', ['title' => 'x', 'year' => 'abc', 'isbn' => '<script>', 'issn' => '1234'])
            ->assertSessionHasErrors(['year', 'isbn', 'issn']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function adminRoutes(): array
    {
        return [
            'dashboard' => ['get', '/admin'],
            'users' => ['get', '/admin/users'],
            'board' => ['get', '/admin/board'],
            'audit log' => ['get', '/admin/audit-log'],
        ];
    }

    #[DataProvider('adminRoutes')]
    public function test_admin_routes_reject_other_roles(string $method, string $uri): void
    {
        foreach ([$this->editor, $this->author] as $user) {
            $this->actingAs($user)->{$method}($uri)->assertForbidden();
        }
    }

    public function test_non_admins_cannot_change_roles(): void
    {
        $this->actingAs($this->author)->put('/admin/users/'.$this->author->id, ['roles' => ['admin']])->assertForbidden();
        $this->assertFalse($this->author->fresh()->hasRole(Role::Admin));
    }

    public function test_editor_routes_reject_authors(): void
    {
        $this->actingAs($this->author)->get('/editor/issues')->assertForbidden();
        $this->actingAs($this->author)->post('/editor/issues', ['title' => 'x', 'year' => 2026])->assertForbidden();
        $this->actingAs($this->author)->get('/editor/articles')->assertForbidden();
    }

    public function test_admin_can_change_roles_and_it_is_audited(): void
    {
        $this->actingAs($this->admin)->put('/admin/users/'.$this->author->id, ['roles' => ['author', 'reviewer']])->assertSessionHasNoErrors();

        $user = $this->author->fresh();
        $this->assertTrue($user->hasRole(Role::Reviewer));
        $this->assertTrue($user->hasRole(Role::Author));

        $log = AuditLog::where('action', 'user.roles_changed')->firstOrFail();
        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertSame($this->author->id, $log->meta['user_id']);
    }

    public function test_invalid_roles_are_rejected(): void
    {
        $this->actingAs($this->admin)->put('/admin/users/'.$this->author->id, ['roles' => ['superuser']])->assertSessionHasErrors('roles.0');
        $this->assertTrue($this->author->fresh()->hasRole(Role::Author));
    }

    public function test_last_administrator_cannot_be_removed(): void
    {
        $this->actingAs($this->admin)->put('/admin/users/'.$this->admin->id, ['roles' => ['editor']])->assertSessionHasErrors('roles');
        $this->assertTrue($this->admin->fresh()->hasRole(Role::Admin));

        $second = $this->userWith(Role::Admin);

        $this->actingAs($this->admin)->put('/admin/users/'.$this->admin->id, ['roles' => ['editor']])->assertSessionHasNoErrors();
        $this->assertFalse($this->admin->fresh()->hasRole(Role::Admin));
        $this->assertTrue($second->fresh()->hasRole(Role::Admin));
    }

    public function test_user_search_treats_sql_as_text(): void
    {
        $this->actingAs($this->admin)->get('/admin/users?'.http_build_query(['q' => "' OR '1'='1"]))
            ->assertOk()
            ->assertDontSee($this->author->email);

        $this->actingAs($this->admin)->get('/admin/users?'.http_build_query(['q' => $this->author->email]))
            ->assertOk()
            ->assertSee($this->author->email);
    }

    public function test_admin_edits_pages_and_html_is_removed_on_display(): void
    {
        $page = Page::create(['slug' => 'about', 'title' => 'About', 'body' => 'Old', 'published_at' => now()]);

        $this->actingAs($this->admin)->put('/admin/pages/'.$page->id, [
            'title' => 'About ICSMI',
            'body' => "## Heading\n\n<script>alert(1)</script>\n\n<iframe src=\"https://evil.test\"></iframe>\n\n[x](javascript:alert(1))",
        ])->assertSessionHasNoErrors();

        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('<h2>Heading</h2>', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_admin_manages_announcements_and_people(): void
    {
        $this->actingAs($this->admin)->post('/admin/announcements', ['title' => 'Draft News', 'body' => 'Isi pengumuman draf.', 'category' => 'news'])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post('/admin/announcements', ['title' => 'Live News', 'body' => '<script>alert(1)</script>', 'category' => 'news', 'published' => '1'])->assertSessionHasNoErrors();

        $response = $this->get('/announcements')->assertOk()->assertSee('Live News')->assertDontSee('Draft News');
        $this->assertStringNotContainsString('<script>alert(1)', $response->getContent());

        $this->actingAs($this->admin)->delete('/admin/announcements/'.Announcement::where('title', 'Live News')->value('id'))->assertSessionHasNoErrors();
        $this->get('/announcements')->assertDontSee('Live News');

        $this->actingAs($this->admin)->post('/admin/board', ['group' => 'speaker', 'name' => 'Keynote Person'])->assertSessionHasNoErrors();
        $this->get('/speakers')->assertSee('Keynote Person');
        $this->get('/editorial-board')->assertDontSee('Keynote Person');

        $this->actingAs($this->admin)->post('/admin/board', ['group' => 'hacker', 'name' => 'x'])->assertSessionHasErrors('group');
        $this->assertSame(1, BoardMember::count());
    }

    public function test_admin_forms_require_csrf_tokens(): void
    {
        $page = Page::create(['slug' => 'about', 'title' => 'About', 'body' => 'Old']);
        $manuscript = $this->manuscript();

        $this->app['env'] = 'local';

        $requests = [
            [$this->admin, 'put', '/admin/users/'.$this->author->id],
            [$this->admin, 'put', '/admin/pages/'.$page->id],
            [$this->admin, 'post', '/admin/announcements'],
            [$this->admin, 'post', '/admin/board'],
            [$this->editor, 'post', '/editor/issues'],
            [$this->editor, 'post', '/editor/articles'],
            [$this->editor, 'post', '/editor/manuscripts/'.$manuscript->id.'/publish'],
        ];

        foreach ($requests as [$user, $method, $uri]) {
            $this->actingAs($user)->withSession(['_token' => 'expected-token'])->{$method}($uri, [])->assertStatus(419);
        }

        $this->assertSame('Old', $page->fresh()->body);
    }

    public function test_audit_log_page_escapes_entries(): void
    {
        AuditLog::record('auth.failed', null, ['email' => '<script>alert(1)</script>']);

        $html = $this->actingAs($this->admin)->get('/admin/audit-log')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)', $html);
    }

    private function reportData(): void
    {
        $issue = Issue::factory()->create(['title' => 'Proceedings 2026', 'year' => 2026]);

        $accepted = $this->manuscript(ManuscriptStatus::Published, [
            'title' => 'Published Paper',
            'submitted_at' => '2026-01-10 08:00:00',
            'decided_at' => '2026-01-20 08:00:00',
        ]);
        $article = Article::factory()->create([
            'issue_id' => $issue->id,
            'manuscript_id' => $accepted->id,
            'title' => 'Published Paper',
            'published_at' => '2026-01-30 08:00:00',
        ]);

        $this->manuscript(ManuscriptStatus::Rejected, ['title' => 'Rejected Paper', 'submitted_at' => '2026-01-15 08:00:00', 'decided_at' => '2026-01-20 08:00:00']);
        $this->manuscript(ManuscriptStatus::Accepted, ['title' => 'Accepted Paper', 'submitted_at' => '2026-02-01 08:00:00', 'decided_at' => '2026-02-11 08:00:00']);
        $this->manuscript(ManuscriptStatus::Submitted, ['title' => 'March Paper', 'submitted_at' => '2026-03-01 08:00:00']);

        ArticleStat::create(['article_id' => $article->id, 'date' => '2026-02-01', 'views' => 10, 'downloads' => 4]);
        ArticleStat::create(['article_id' => $article->id, 'date' => '2026-03-01', 'views' => 5, 'downloads' => 1]);

        $reviewer = $this->userWith(Role::Reviewer, ['name' => 'Workload Reviewer']);

        foreach ([ReviewStatus::Completed, ReviewStatus::Declined] as $status) {
            ReviewAssignment::forceCreate([
                'manuscript_id' => $accepted->id,
                'reviewer_id' => $reviewer->id,
                'assigned_by' => $this->editor->id,
                'round' => $status === ReviewStatus::Completed ? 1 : 2,
                'status' => $status,
                'due_at' => '2026-02-01',
                'responded_at' => '2026-01-12 08:00:00',
                'completed_at' => $status === ReviewStatus::Completed ? '2026-01-16 08:00:00' : null,
                'created_at' => '2026-01-12 08:00:00',
                'updated_at' => '2026-01-12 08:00:00',
            ]);
        }
    }

    public function test_report_figures_are_correct(): void
    {
        $this->reportData();

        $response = $this->actingAs($this->editor)->get('/reports')->assertOk();
        $summary = $response->viewData('summary');

        $this->assertSame(4, $summary['total']);
        $this->assertSame(2, $summary['accepted']);
        $this->assertSame(1, $summary['rejected']);
        $this->assertSame(66.7, $summary['acceptance_rate']);
        $this->assertSame(33.3, $summary['rejection_rate']);
        $this->assertEquals(8.3, $summary['days_to_decision']);
        $this->assertEquals(20.0, $summary['days_to_publication']);
        $this->assertSame(15, $summary['views']);
        $this->assertSame(5, $summary['downloads']);

        $sections = $response->viewData('sections');

        $this->assertSame([['2026-01', 2], ['2026-02', 1], ['2026-03', 1]], $sections['monthly']['rows']);
        $this->assertContains(['Published', 1], $sections['statuses']['rows']);
        $this->assertSame(['Proceedings 2026', 2026, 1, 1, 15, 5], $sections['issues']['rows'][0]);
        $this->assertSame([15, 5], array_slice($sections['articles']['rows'][0], 3));
        $this->assertSame('Workload Reviewer', $sections['reviewers']['rows'][0][0]);
        $this->assertSame([2, 1, 1, 1, 4.0], array_slice($sections['reviewers']['rows'][0], 2));
    }

    public function test_reports_filter_by_date_range(): void
    {
        $this->reportData();

        $summary = $this->actingAs($this->editor)->get('/reports?from=2026-02-01&to=2026-02-28')->assertOk()->viewData('summary');

        $this->assertSame(1, $summary['total']);
        $this->assertSame(10, $summary['views']);
        $this->assertSame(4, $summary['downloads']);
    }

    public function test_report_filters_reject_invalid_dates(): void
    {
        $this->actingAs($this->editor)->get('/reports?'.http_build_query(['from' => "2026-01-01' OR 1=1 --"]))->assertSessionHasErrors('from');
        $this->actingAs($this->editor)->get('/reports?from=2026-03-01&to=2026-01-01')->assertSessionHasErrors('to');
    }

    public function test_reports_are_limited_to_admins_and_editors(): void
    {
        $this->get('/reports')->assertRedirect('/login');

        $reviewer = $this->userWith(Role::Reviewer);

        foreach ([$this->author, $reviewer] as $user) {
            $this->actingAs($user)->get('/reports')->assertForbidden();
            $this->actingAs($user)->get('/reports/export/manuscripts.csv')->assertForbidden();
        }

        $this->actingAs($this->admin)->get('/reports')->assertOk();
    }

    public function test_only_known_sections_and_formats_can_be_exported(): void
    {
        $this->actingAs($this->editor)->get('/reports/export/users.csv')->assertNotFound();
        $this->actingAs($this->editor)->get('/reports/export/manuscripts.php')->assertNotFound();
    }

    public function test_csv_export_contains_rows_and_is_audited(): void
    {
        $this->reportData();

        $response = $this->actingAs($this->editor)->get('/reports/export/manuscripts.csv')->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $response->assertDownload('icsmi-manuscripts-'.now()->format('Ymd').'.csv');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Code,Title,Status', $csv);
        $this->assertStringContainsString('Published Paper', $csv);

        $log = AuditLog::where('action', 'report.exported')->firstOrFail();
        $this->assertSame($this->editor->id, $log->user_id);
        $this->assertSame('manuscripts', $log->meta['section']);
    }

    public function test_exports_neutralize_spreadsheet_formulas(): void
    {
        foreach (['=HYPERLINK("http://evil.test","x")', '+1+1', '-1+1', '@SUM(A1)'] as $index => $title) {
            $this->manuscript(ManuscriptStatus::Submitted, ['code' => 'F'.$index, 'title' => $title]);
        }

        $csv = $this->actingAs($this->editor)->get('/reports/export/manuscripts.csv')->assertOk()->streamedContent();

        $this->assertStringContainsString('"\'=HYPERLINK', $csv);
        $this->assertStringContainsString(",'+1+1,", $csv);
        $this->assertStringContainsString(",'-1+1,", $csv);
        $this->assertStringContainsString(",'@SUM(A1),", $csv);
    }

    public function test_excel_export_is_a_real_workbook(): void
    {
        $this->reportData();

        $response = $this->actingAs($this->admin)->get('/reports/export/reviewers.xlsx')->assertOk();
        $file = $response->getFile()->getPathname();

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($file) === true);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $strings = (string) $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        $this->assertNotFalse($sheet);
        $this->assertStringContainsString('Workload Reviewer', $strings.$sheet);
    }

    public function test_report_tables_escape_titles(): void
    {
        $this->manuscript(ManuscriptStatus::Submitted, ['title' => '<script>alert(1)</script>']);

        $html = $this->actingAs($this->editor)->get('/reports')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert(1)', $html);
    }
}
