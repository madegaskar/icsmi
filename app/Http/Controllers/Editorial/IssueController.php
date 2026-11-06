<?php

namespace App\Http\Controllers\Editorial;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Issue;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IssueController extends Controller
{
    public function index(): View
    {
        return view('editor.issues.index', [
            'issues' => Issue::withCount('articles')->orderByDesc('year')->orderByDesc('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('editor.issues.form', ['issue' => new Issue]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $issue = Issue::create($data + ['slug' => UniqueSlug::for(Issue::class, $data['title'])]);

        AuditLog::record('issue.created', $request->user()->id, ['issue_id' => $issue->id]);

        return redirect()->route('editor.issues.index')->with('status', 'Proceedings created.');
    }

    public function edit(Issue $issue): View
    {
        return view('editor.issues.form', ['issue' => $issue]);
    }

    public function update(Request $request, Issue $issue): RedirectResponse
    {
        $issue->update($this->validated($request, $issue));

        AuditLog::record('issue.updated', $request->user()->id, ['issue_id' => $issue->id]);

        return redirect()->route('editor.issues.index')->with('status', 'Proceedings updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Issue $issue = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'between:1900,2100'],
            'volume' => ['nullable', 'integer', 'min:1'],
            'number' => ['nullable', 'integer', 'min:1'],
            'conference_dates' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'regex:/^[\d-]{10,17}X?$/'],
            'issn' => ['nullable', 'string', 'regex:/^\d{4}-\d{3}[\dX]$/'],
            'description' => ['nullable', 'string', 'max:5000'],
            'published' => ['nullable', 'boolean'],
        ]);

        $published = (bool) ($data['published'] ?? false);
        unset($data['published']);

        return $data + ['published_at' => $published ? ($issue?->published_at ?? now()) : null];
    }
}
