<?php

namespace App\Http\Controllers\Workflow;

use App\Enums\ManuscriptStatus;
use App\Enums\ReviewStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Issue;
use App\Models\Manuscript;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Notifications\ReviewInvitation;
use App\Rules\ManuscriptDocument;
use App\Services\ArticlePublisher;
use App\Services\ManuscriptWorkflow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EditorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', Rule::enum(ManuscriptStatus::class)]]);

        return view('editor.index', [
            'manuscripts' => Manuscript::query()
                ->notAuthoredBy($request->user())
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->latest('submitted_at')
                ->paginate(20)
                ->withQueryString(),
            'status' => $filters['status'] ?? null,
        ]);
    }

    public function show(Manuscript $manuscript): View
    {
        Gate::authorize('manage', $manuscript);

        $manuscript->load(['authors', 'files', 'histories.actor', 'assignments.reviewer', 'submitter']);

        return view('editor.show', [
            'manuscript' => $manuscript,
            'transitions' => $manuscript->status->editorTransitions(),
            'candidates' => $this->reviewerCandidates($manuscript),
            'issues' => Issue::orderByDesc('year')->orderByDesc('id')->get(['id', 'title', 'published_at']),
        ]);
    }

    public function updateStatus(Request $request, Manuscript $manuscript, ManuscriptWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('manage', $manuscript);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ManuscriptStatus::class)],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $workflow->transition($manuscript, ManuscriptStatus::from($validated['status']), $request->user(), $validated['note'] ?? null);

        return redirect()->route('editor.manuscripts.show', $manuscript)->with('status', 'Status updated.');
    }

    public function publish(Request $request, Manuscript $manuscript, ArticlePublisher $publisher): RedirectResponse
    {
        Gate::authorize('manage', $manuscript);

        $validated = $request->validate([
            'issue_id' => ['required', 'integer', Rule::exists('issues', 'id')],
            'doi' => ['nullable', 'string', 'max:255', 'regex:/^10\.\d{4,9}\/\S+$/', Rule::unique('articles', 'doi')],
            'first_page' => ['nullable', 'integer', 'min:1', 'required_with:last_page'],
            'last_page' => ['nullable', 'integer', 'gte:first_page'],
            'pdf' => ['required', 'file', 'max:20480', new ManuscriptDocument(['pdf'])],
        ]);

        $publisher->publish($manuscript->load('authors'), $request->user(), $validated, $request->file('pdf'));

        return redirect()->route('editor.manuscripts.show', $manuscript)->with('status', 'Article published.');
    }

    public function assignReviewer(Request $request, Manuscript $manuscript): RedirectResponse
    {
        Gate::authorize('manage', $manuscript);

        if ($manuscript->status !== ManuscriptStatus::InReview) {
            throw ValidationException::withMessages(['reviewer_id' => 'Reviewers can only be assigned while the manuscript is in review.']);
        }

        $validated = $request->validate([
            'reviewer_id' => ['required', 'integer', Rule::in($this->reviewerCandidates($manuscript)->pluck('id'))],
            'due_at' => ['required', 'date', 'after:today'],
        ]);

        $assignment = $manuscript->assignments()->create([
            'reviewer_id' => $validated['reviewer_id'],
            'assigned_by' => $request->user()->id,
            'round' => $manuscript->current_version,
            'status' => ReviewStatus::Invited,
            'due_at' => $validated['due_at'],
        ]);

        AuditLog::record('review.assigned', $request->user()->id, ['assignment_id' => $assignment->id, 'manuscript_id' => $manuscript->id]);

        rescue(fn () => $assignment->reviewer->notify(new ReviewInvitation($assignment)));

        return redirect()->route('editor.manuscripts.show', $manuscript)->with('status', 'Reviewer invited.');
    }

    public function cancelAssignment(Request $request, ReviewAssignment $assignment): RedirectResponse
    {
        Gate::authorize('manage', $assignment->manuscript);

        if (! in_array($assignment->status, [ReviewStatus::Invited, ReviewStatus::Accepted], true)) {
            throw ValidationException::withMessages(['assignment' => 'This assignment can no longer be cancelled.']);
        }

        $assignment->update(['status' => ReviewStatus::Cancelled]);

        AuditLog::record('review.cancelled', $request->user()->id, ['assignment_id' => $assignment->id]);

        return redirect()->route('editor.manuscripts.show', $assignment->manuscript)->with('status', 'Assignment cancelled.');
    }

    /**
     * Verified reviewers who did not write the manuscript and are not yet assigned this round.
     *
     * @return Collection<int, User>
     */
    private function reviewerCandidates(Manuscript $manuscript): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($roles) => $roles->where('role', Role::Reviewer))
            ->whereNotNull('email_verified_at')
            ->whereDoesntHave('reviewAssignments', fn ($assignments) => $assignments
                ->where('manuscript_id', $manuscript->id)
                ->where('round', $manuscript->current_version))
            ->orderBy('name')
            ->get()
            ->reject(fn (User $user) => $manuscript->isAuthoredBy($user))
            ->values();
    }
}
