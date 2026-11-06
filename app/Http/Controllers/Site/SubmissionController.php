<?php

namespace App\Http\Controllers\Site;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PublicSubmission;
use App\Models\User;
use App\Notifications\ConfirmPublicSubmission;
use App\Rules\ManuscriptDocument;
use App\Services\ManuscriptWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function create(): View
    {
        return view('site.submission.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // A filled hidden field means a bot: answer as usual and keep nothing.
        if ($request->filled('website')) {
            return redirect()->route('submission.create')->with('submission_received', true);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{6,30}$/'],
            'institution_address' => ['required', 'string', 'max:500'],
            'abstract' => ['required', 'string', 'max:5000'],
            'keywords' => ['required', 'string', 'max:500'],
            'file' => ['required', 'file', 'max:20480', new ManuscriptDocument],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = Storage::disk('local')->putFileAs('public-submissions', $file, Str::random(40).'.'.$extension);

        $submission = DB::transaction(function () use ($data, $file, $extension, $path) {
            $submission = PublicSubmission::create([
                'title' => $data['title'],
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'phone' => $data['phone'],
                'institution_address' => $data['institution_address'],
                'abstract' => $data['abstract'],
                'keywords' => $data['keywords'],
                'path' => $path,
                'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
                'extension' => $extension,
                'size' => $file->getSize(),
                'sha256' => hash_file('sha256', $file->getRealPath()),
            ]);

            $submission->update(['code' => sprintf('PUB-%d-%04d', now()->year, $submission->id)]);

            return $submission;
        });

        AuditLog::record('public_submission.created', null, ['submission_id' => $submission->id]);

        $sent = rescue(function () use ($submission) {
            Notification::route('mail', $submission->email)->notify(new ConfirmPublicSubmission($submission));

            return true;
        }, false);

        return redirect()->route('submission.create')
            ->with('submission_received', true)
            ->with('submission_code', $submission->code)
            ->with('submission_mail_failed', ! $sent);
    }

    public function confirmForm(Request $request, PublicSubmission $submission): View|RedirectResponse
    {
        if ($submission->confirmed_at) {
            return redirect()->route('login')->with('status', 'This submission is already confirmed. Log in to follow its progress.');
        }

        $account = $this->accountFor($submission);

        if ($account && ! $request->user()) {
            return redirect()->guest(route('login'))->with('status', 'An account already uses this email. Log in to confirm your submission.');
        }

        $this->ensureSignedInUserMatches($request, $account);

        return view('site.submission.confirm', ['submission' => $submission, 'hasAccount' => (bool) $account]);
    }

    public function confirm(Request $request, PublicSubmission $submission, ManuscriptWorkflow $workflow): RedirectResponse
    {
        if ($submission->confirmed_at) {
            return redirect()->route('login')->with('status', 'This submission is already confirmed. Log in to follow its progress.');
        }

        $account = $this->accountFor($submission);

        abort_if($account && ! $request->user(), 403, 'Log in to confirm this submission.');

        $this->ensureSignedInUserMatches($request, $account);

        $password = $account ? null : $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ])['password'];

        [$user, $manuscript] = DB::transaction(function () use ($submission, $account, $password, $workflow) {
            $user = $account ?? User::create([
                'name' => $submission->name,
                'email' => $submission->email,
                'password' => $password,
                'affiliation' => mb_substr($submission->institution_address, 0, 255),
            ]);

            // The signed link reached this mailbox, which proves the address belongs to its owner.
            $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();
            $user->assignRole(Role::Author);

            return [$user, $workflow->adoptPublicSubmission($submission, $user)];
        });

        AuditLog::record('public_submission.confirmed', $user->id, ['submission_id' => $submission->id, 'manuscript_id' => $manuscript->id]);

        $workflow->notifyEditorsOfSubmission($manuscript);

        if (! $request->user()) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return redirect()->route('author.manuscripts.show', $manuscript)->with('status', 'Submission confirmed. It is now with the editors.');
    }

    private function accountFor(PublicSubmission $submission): ?User
    {
        return User::whereRaw('LOWER(email) = ?', [Str::lower($submission->email)])->first();
    }

    private function ensureSignedInUserMatches(Request $request, ?User $account): void
    {
        abort_if($request->user() && $request->user()->id !== $account?->id, 403, 'This submission belongs to another email address. Log out first.');
    }
}
