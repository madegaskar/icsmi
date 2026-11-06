<?php

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManuscriptRequest;
use App\Models\Manuscript;
use App\Rules\ManuscriptDocument;
use App\Services\ManuscriptWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuthorManuscriptController extends Controller
{
    public function index(Request $request): View
    {
        return view('author.index', [
            'manuscripts' => Manuscript::where('submitter_id', $request->user()->id)->latest('submitted_at')->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        return view('author.create', ['user' => $request->user(), 'maxAuthors' => StoreManuscriptRequest::MAX_AUTHORS]);
    }

    public function store(StoreManuscriptRequest $request, ManuscriptWorkflow $workflow): RedirectResponse
    {
        $manuscript = $workflow->submit(
            $request->user(),
            $request->safe()->only(['title', 'abstract', 'keywords', 'authors']),
            $request->file('blinded_file'),
            $request->file('full_file'),
        );

        return redirect()->route('author.manuscripts.show', $manuscript)->with('status', 'Manuscript submitted.');
    }

    public function show(Manuscript $manuscript): View
    {
        Gate::authorize('view', $manuscript);

        abort_unless($manuscript->submitter_id === request()->user()->id, 403);

        return view('author.show', [
            'manuscript' => $manuscript->load(['authors', 'files', 'histories']),
            'reviews' => $manuscript->reviewsVisibleToAuthor(),
        ]);
    }

    public function revise(Request $request, Manuscript $manuscript, ManuscriptWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('revise', $manuscript);

        $validated = $request->validate([
            'blinded_file' => ['required', 'file', 'max:20480', new ManuscriptDocument],
            'full_file' => ['nullable', 'file', 'max:20480', new ManuscriptDocument],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        $workflow->submitRevision($manuscript, $request->user(), $request->file('blinded_file'), $request->file('full_file'), $validated['note'] ?? null);

        return redirect()->route('author.manuscripts.show', $manuscript)->with('status', 'Revision submitted.');
    }
}
