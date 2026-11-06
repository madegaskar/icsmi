<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStatus;
use App\Enums\Role;
use App\Models\Manuscript;
use App\Models\ManuscriptFile;
use App\Models\PublicSubmission;
use App\Models\User;
use App\Notifications\ConfirmPublicSubmission;
use App\Notifications\ManuscriptSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Tests\TestCase;

class PublicSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    private const PASSWORD = 'a-valid-test-value';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Notification::fake();

        $this->editor = User::factory()->create();
        $this->editor->assignRole(Role::Editor);
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'title' => 'A Public Paper',
            'name' => 'Public Sender',
            'email' => 'sender@example.com',
            'phone' => '+62 812-3456-7890',
            'institution_address' => 'Jl. Contoh No. 1, Jakarta',
            'abstract' => 'An abstract.',
            'keywords' => 'green, innovation',
            'file' => UploadedFile::fake()->createWithContent('my paper.pdf', "%PDF-1.4\ncontent\n%%EOF"),
        ];
    }

    private function submit(array $overrides = []): PublicSubmission
    {
        $this->post('/submission', $this->payload($overrides))->assertSessionHasNoErrors();

        return PublicSubmission::latest('id')->firstOrFail();
    }

    private function link(PublicSubmission $submission): string
    {
        return URL::temporarySignedRoute('submission.confirm', now()->addDay(), ['submission' => $submission->id]);
    }

    private function confirmWithPassword(string $link): \Illuminate\Testing\TestResponse
    {
        return $this->post($link, ['password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD]);
    }

    public function test_form_is_available_without_login_and_shows_every_field(): void
    {
        $response = $this->get('/submission')->assertOk();

        foreach (['Title', 'Name', 'Email', 'Phone', 'Institution Address', 'Abstract', 'Keyword', 'Choose File'] as $label) {
            $response->assertSee($label);
        }

        $this->assertGuest();
    }

    public function test_submission_waits_for_confirmation_before_reaching_editors(): void
    {
        $submission = $this->submit(['email' => 'Sender@Example.com']);

        $this->assertSame(sprintf('PUB-%d-%04d', now()->year, $submission->id), $submission->code);
        $this->assertSame('sender@example.com', $submission->email);
        $this->assertNull($submission->confirmed_at);
        Storage::disk('local')->assertExists($submission->path);
        $this->assertStringNotContainsString('paper', $submission->path);

        $this->assertDatabaseCount('manuscripts', 0);
        $this->assertDatabaseMissing('users', ['email' => 'sender@example.com']);
        $this->assertGuest();

        $this->get('/submission')->assertSee($submission->code)->assertSee('check your email');

        Notification::assertSentOnDemand(ConfirmPublicSubmission::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'sender@example.com');
        Notification::assertNotSentTo($this->editor, ManuscriptSubmitted::class);
    }

    public function test_confirmation_email_carries_no_text_from_the_form(): void
    {
        $submission = $this->submit(['title' => 'Buy cheap pills at spam.test', 'name' => 'Spam Sender', 'abstract' => 'spam abstract']);

        $mail = json_encode((new ConfirmPublicSubmission($submission))->toMail(new AnonymousNotifiable)->toArray());

        $this->assertStringNotContainsString('spam', strtolower($mail));
        $this->assertStringContainsString('signature', $mail);
    }

    public function test_confirming_creates_an_author_and_a_regular_manuscript(): void
    {
        $submission = $this->submit();
        $originalPath = $submission->path;

        $response = $this->get($this->link($submission))->assertOk();
        $response->assertSee('A Public Paper');
        $this->assertDatabaseCount('manuscripts', 0);

        $this->confirmWithPassword($this->link($submission))->assertSessionHasNoErrors();

        $user = User::where('email', 'sender@example.com')->firstOrFail();
        $manuscript = Manuscript::firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole(Role::Author));
        $this->assertFalse($user->hasRole(Role::Admin, Role::Editor, Role::Reviewer));
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));

        $this->assertSame($user->id, $manuscript->submitter_id);
        $this->assertSame('A Public Paper', $manuscript->title);
        $this->assertSame(ManuscriptStatus::Submitted, $manuscript->status);
        $this->assertSame('Public Sender', $manuscript->authors->first()->name);
        $this->assertTrue($manuscript->authors->first()->is_corresponding);

        $file = $manuscript->files->first();
        $this->assertSame(ManuscriptFile::KIND_BLINDED, $file->kind);
        Storage::disk('local')->assertExists($file->path);
        Storage::disk('local')->assertMissing($originalPath);

        $submission->refresh();
        $this->assertNotNull($submission->confirmed_at);
        $this->assertSame($manuscript->id, $submission->manuscript_id);

        Notification::assertSentTo($this->editor, ManuscriptSubmitted::class);
        $this->assertDatabaseHas('audit_logs', ['action' => 'public_submission.confirmed', 'user_id' => $user->id]);

        $this->get('/author')->assertOk()->assertSee('A Public Paper');
        $this->get('/author/manuscripts/'.$manuscript->id)->assertOk();
        $this->get('/manuscript-files/'.$file->id)->assertOk();
    }

    public function test_confirmed_manuscript_continues_through_the_editor_workflow(): void
    {
        $this->confirmWithPassword($this->link($this->submit()));
        $manuscript = Manuscript::firstOrFail();

        $this->actingAs($this->editor)
            ->get('/editor/manuscripts/'.$manuscript->id)
            ->assertOk()
            ->assertSee('+62 812-3456-7890');

        $this->actingAs($this->editor)
            ->post('/editor/manuscripts/'.$manuscript->id.'/status', ['status' => 'screening'])
            ->assertSessionHasNoErrors();

        $this->assertSame(ManuscriptStatus::Screening, $manuscript->fresh()->status);
    }

    public function test_reviewers_do_not_see_public_form_details(): void
    {
        $this->confirmWithPassword($this->link($this->submit()));
        $manuscript = Manuscript::firstOrFail();
        $manuscript->update(['status' => ManuscriptStatus::InReview]);

        $reviewer = User::factory()->create();
        $reviewer->assignRole(Role::Reviewer);

        $this->actingAs($this->editor)->post('/editor/manuscripts/'.$manuscript->id.'/reviewers', ['reviewer_id' => $reviewer->id, 'due_at' => now()->addWeek()->toDateString()])->assertSessionHasNoErrors();
        $assignment = $manuscript->assignments()->firstOrFail();

        $html = $this->actingAs($reviewer)->get('/reviewer/assignments/'.$assignment->id)->assertOk()->getContent();

        $this->assertStringNotContainsString('Public Sender', $html);
        $this->assertStringNotContainsString('sender@example.com', $html);
        $this->assertStringNotContainsString('Jl. Contoh', $html);
        $this->assertStringNotContainsString('812-3456', $html);
    }

    public function test_unsigned_tampered_and_expired_links_are_rejected(): void
    {
        $submission = $this->submit();
        $other = $this->submit(['email' => 'other@example.com']);
        $valid = $this->link($submission);

        $this->get('/submission/confirm/'.$submission->id)->assertForbidden();
        $this->post('/submission/confirm/'.$submission->id, ['password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD])->assertForbidden();
        $this->get(str_replace('/confirm/'.$submission->id, '/confirm/'.$other->id, $valid))->assertForbidden();

        $expired = URL::temporarySignedRoute('submission.confirm', now()->subMinute(), ['submission' => $submission->id]);
        $this->get($expired)->assertForbidden();

        $this->assertDatabaseCount('manuscripts', 0);
    }

    public function test_link_cannot_be_used_twice_or_to_sign_in_again(): void
    {
        $submission = $this->submit();
        $link = $this->link($submission);

        $this->confirmWithPassword($link);
        $this->post('/logout');
        $this->assertGuest();

        $this->get($link)->assertRedirect('/login');
        $this->confirmWithPassword($link)->assertRedirect('/login');

        $this->assertGuest();
        $this->assertDatabaseCount('manuscripts', 1);
    }

    public function test_existing_account_must_log_in_and_cannot_be_taken_over(): void
    {
        $owner = User::factory()->create(['email' => 'sender@example.com', 'password' => 'owner-original-value']);
        $submission = $this->submit();
        $link = $this->link($submission);

        $this->get($link)->assertRedirect('/login');

        $this->confirmWithPassword($link)->assertForbidden();

        $this->assertGuest();
        $this->assertDatabaseCount('manuscripts', 0);
        $this->assertTrue(Hash::check('owner-original-value', $owner->fresh()->password));

        $this->actingAs($owner)->get($link)->assertOk()->assertDontSee('name="password"', false);
        $this->actingAs($owner)->post($link)->assertRedirect();

        $this->assertSame($owner->id, Manuscript::firstOrFail()->submitter_id);
        $this->assertTrue($owner->fresh()->hasRole(Role::Author));
        $this->assertTrue(Hash::check('owner-original-value', $owner->fresh()->password));
    }

    public function test_signed_in_user_with_another_email_cannot_confirm(): void
    {
        $submission = $this->submit();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get($this->link($submission))->assertForbidden();
        $this->actingAs($stranger)->post($this->link($submission), ['password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD])->assertForbidden();

        $this->assertDatabaseCount('manuscripts', 0);
        $this->assertDatabaseMissing('users', ['email' => 'sender@example.com']);
    }

    public function test_a_submission_can_only_be_adopted_once(): void
    {
        $submission = $this->submit();
        $user = User::factory()->create();
        $workflow = app(\App\Services\ManuscriptWorkflow::class);

        $workflow->adoptPublicSubmission($submission, $user);

        try {
            $workflow->adoptPublicSubmission($submission, $user);
            $this->fail('The second adoption should have been refused.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

        $this->assertSame(1, Manuscript::where('submitter_id', $user->id)->count());
    }

    public function test_weak_or_mismatched_password_creates_nothing(): void
    {
        $link = $this->link($this->submit());

        $this->post($link, ['password' => self::PASSWORD, 'password_confirmation' => 'different-value'])->assertSessionHasErrors('password');
        $this->post($link, ['password' => 'x', 'password_confirmation' => 'x'])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'sender@example.com']);
        $this->assertDatabaseCount('manuscripts', 0);
    }

    public function test_form_cannot_grant_roles_or_choose_the_account(): void
    {
        $link = $this->link($this->submit());

        $this->post($link, [
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
            'role' => 'admin',
            'roles' => ['admin'],
            'email' => 'attacker@example.com',
        ]);

        $user = User::where('email', 'sender@example.com')->firstOrFail();
        $this->assertFalse($user->hasRole(Role::Admin));
        $this->assertDatabaseMissing('users', ['email' => 'attacker@example.com']);
    }

    public function test_mail_failure_is_reported_to_the_sender_and_keeps_the_submission(): void
    {
        Notification::shouldReceive('route')->andThrow(new RuntimeException('smtp down'));

        $this->post('/submission', $this->payload())->assertSessionHas('submission_mail_failed', true);

        $this->assertDatabaseCount('public_submissions', 1);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->post('/submission', [])
            ->assertSessionHasErrors(['title', 'name', 'email', 'phone', 'institution_address', 'abstract', 'keywords', 'file']);

        $this->post('/submission', $this->payload(['email' => 'not-an-email', 'phone' => '<script>']))
            ->assertSessionHasErrors(['email', 'phone']);

        $this->assertDatabaseCount('public_submissions', 0);
    }

    public function test_dangerous_files_are_rejected(): void
    {
        $files = [
            UploadedFile::fake()->createWithContent('shell.pdf', '<?php system($_GET["c"]);'),
            UploadedFile::fake()->createWithContent('shell.php', "%PDF-1.4\n"),
            UploadedFile::fake()->createWithContent('page.html', "%PDF-1.4\n"),
            UploadedFile::fake()->createWithContent('big.pdf', "%PDF-1.4\n")->size(20481),
        ];

        foreach ($files as $file) {
            $this->post('/submission', $this->payload(['file' => $file]))->assertSessionHasErrors('file');
        }

        $this->assertDatabaseCount('public_submissions', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_requests_without_csrf_token_are_rejected(): void
    {
        $link = $this->link($this->submit());

        $this->app['env'] = 'local';

        $this->withSession(['_token' => 'expected-token'])->post('/submission', $this->payload())->assertStatus(419);
        $this->withSession(['_token' => 'expected-token'])->post($link, ['password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD])->assertStatus(419);

        $this->assertDatabaseCount('public_submissions', 1);
        $this->assertDatabaseCount('manuscripts', 0);
    }

    public function test_honeypot_keeps_nothing_but_looks_successful(): void
    {
        $this->post('/submission', $this->payload(['website' => 'http://spam.test']))
            ->assertSessionHas('submission_received', true);

        $this->assertDatabaseCount('public_submissions', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        Notification::assertNothingSent();
    }

    public function test_submissions_are_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post('/submission', $this->payload(['email' => 'sender'.$attempt.'@example.com']))->assertRedirect();
        }

        $this->post('/submission', $this->payload(['email' => 'sender6@example.com']))->assertTooManyRequests();
        $this->assertDatabaseCount('public_submissions', 5);
    }

    public function test_confirmation_mail_to_one_address_is_limited_per_day(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.'.$attempt])->post('/submission', $this->payload(['email' => 'victim@example.com']))->assertRedirect();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.99'])->post('/submission', $this->payload(['email' => 'VICTIM@example.com']))->assertTooManyRequests();

        Notification::assertSentOnDemandTimes(ConfirmPublicSubmission::class, 5);
    }

    public function test_unconfirmed_submissions_expire_and_lose_their_files(): void
    {
        $old = $this->submit(['email' => 'old@example.com']);
        $old->forceFill(['created_at' => now()->subDays(8)])->save();
        $recent = $this->submit(['email' => 'recent@example.com']);
        $confirmed = $this->submit(['email' => 'done@example.com']);
        $this->confirmWithPassword($this->link($confirmed));
        $confirmed->refresh()->forceFill(['created_at' => now()->subDays(30)])->save();

        $this->artisan('icsmi:purge-unconfirmed')->assertSuccessful();

        $this->assertDatabaseMissing('public_submissions', ['id' => $old->id]);
        Storage::disk('local')->assertMissing($old->path);
        $this->assertDatabaseHas('public_submissions', ['id' => $recent->id]);
        $this->assertDatabaseHas('public_submissions', ['id' => $confirmed->id]);
        Storage::disk('local')->assertExists($confirmed->path);
    }

    public function test_sql_payload_is_stored_as_text_and_html_is_escaped_for_editors(): void
    {
        $sql = "x'); DROP TABLE users; --";
        $xss = '"><script>alert(1)</script>';

        $submission = $this->submit(['title' => $xss, 'name' => $xss, 'abstract' => $sql, 'keywords' => $xss, 'institution_address' => $xss]);

        $this->assertSame($sql, $submission->abstract);
        $this->assertDatabaseCount('users', 1);

        foreach (['/editor/submissions', '/editor/submissions/'.$submission->id] as $uri) {
            $html = $this->actingAs($this->editor)->get($uri)->assertOk()->getContent();

            $this->assertStringNotContainsString('<script>alert(1)', $html);
        }

        auth()->logout();

        $html = $this->get($this->link($submission))->assertOk()->getContent();
        $this->assertStringNotContainsString('<script>alert(1)', $html);
    }

    public function test_only_editors_can_read_submissions_and_files(): void
    {
        $submission = $this->submit();

        $uris = ['/editor/submissions', '/editor/submissions/'.$submission->id, '/editor/submissions/'.$submission->id.'/file'];

        foreach ($uris as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }

        $this->get('/storage/'.$submission->path)->assertNotFound();

        foreach ([Role::Author, Role::Reviewer, Role::Admin] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);

            foreach ($uris as $uri) {
                $this->actingAs($user)->get($uri)->assertForbidden();
            }
        }

        $this->actingAs($this->editor)->get('/editor/submissions/'.$submission->id.'/file')
            ->assertOk()
            ->assertDownload($submission->code.'.pdf');
    }

    public function test_editor_file_link_keeps_working_after_confirmation(): void
    {
        $submission = $this->submit();
        $this->confirmWithPassword($this->link($submission));
        auth()->logout();

        $this->actingAs($this->editor)->get('/editor/submissions/'.$submission->id.'/file')->assertOk();
        $this->actingAs($this->editor)->get('/editor/submissions/'.$submission->id)->assertOk()->assertSee(Manuscript::firstOrFail()->code);
    }
}
