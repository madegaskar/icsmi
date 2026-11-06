<?php

namespace App\Http\Controllers\Workflow;

use App\Enums\ReviewRecommendation;
use App\Enums\ReviewStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ManuscriptFile;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Notifications\ReviewUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewerController extends Controller
{
    public function index(Request $request): View
    {
        return view('reviewer.index', [
            'assignments' => ReviewAssignment::with('manuscript:id,code,title')
                ->where('reviewer_id', $request->user()->id)
                ->where('status', '!=', ReviewStatus::Cancelled)
                ->latest()
                ->paginate(20),
        ]);
    }

    public function show(ReviewAssignment $assignment): View
    {
        Gate::authorize('view', $assignment);

        return view('reviewer.show', [
            'assignment' => $assignment,
            'manuscript' => $assignment->manuscript,
            'file' => ManuscriptFile::where('manuscript_id', $assignment->manuscript_id)
                ->where('version', $assignment->round)
                ->where('kind', ManuscriptFile::KIND_BLINDED)
                ->first(),
        ]);
    }

    public function respond(Request $request, ReviewAssignment $assignment): RedirectResponse
    {
        Gate::authorize('respond', $assignment);

        $validated = $request->validate(['response' => ['required', Rule::in(['accept', 'decline'])]]);

        $assignment->update([
            'status' => $validated['response'] === 'accept' ? ReviewStatus::Accepted : ReviewStatus::Declined,
            'responded_at' => now(),
        ]);

        $this->recordAndNotify($assignment, $request->user(), 'review.responded');

        return $validated['response'] === 'accept'
            ? redirect()->route('reviewer.assignments.show', $assignment)->with('status', 'Invitation accepted.')
            : redirect()->route('reviewer.dashboard')->with('status', 'Invitation declined.');
    }

    public function submit(Request $request, ReviewAssignment $assignment): RedirectResponse
    {
        Gate::authorize('submit', $assignment);

        $validated = $request->validate([
            'recommendation' => ['required', Rule::enum(ReviewRecommendation::class)],
            'comments_for_author' => ['required', 'string', 'max:20000'],
            'comments_for_editor' => ['nullable', 'string', 'max:20000'],
        ]);

        $assignment->update($validated + ['status' => ReviewStatus::Completed, 'completed_at' => now()]);

        $this->recordAndNotify($assignment, $request->user(), 'review.submitted');

        return redirect()->route('reviewer.assignments.show', $assignment)->with('status', 'Review submitted.');
    }

    private function recordAndNotify(ReviewAssignment $assignment, User $reviewer, string $action): void
    {
        AuditLog::record($action, $reviewer->id, ['assignment_id' => $assignment->id, 'status' => $assignment->status->value]);

        $editors = User::whereHas('roles', fn ($roles) => $roles->where('role', Role::Editor))->get();

        foreach ($editors as $editor) {
            if (! $assignment->manuscript->isAuthoredBy($editor)) {
                rescue(fn () => $editor->notify(new ReviewUpdated($assignment)));
            }
        }
    }
}
