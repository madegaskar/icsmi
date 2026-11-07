<?php

namespace App\Http\Controllers\Editorial;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Issue;
use App\Models\Media;
use App\Services\MediaUploader;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IssueController extends Controller
{
    public function __construct(private readonly MediaUploader $uploader) {}

    public function index(): View
    {
        return view('editor.issues.index', [
            'issues' => Issue::withCount('articles')->orderByDesc('year')->orderByDesc('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Issue);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->withCover($request, $this->validated($request));

        $issue = Issue::create($data + ['slug' => UniqueSlug::for(Issue::class, $data['title'])]);

        AuditLog::record('issue.created', $request->user()->id, ['issue_id' => $issue->id]);

        return redirect()->route('editor.issues.index')->with('status', 'Proceedings created.');
    }

    public function edit(Issue $issue): View
    {
        return $this->form($issue);
    }

    public function update(Request $request, Issue $issue): RedirectResponse
    {
        $issue->update($this->withCover($request, $this->validated($request, $issue)));

        AuditLog::record('issue.updated', $request->user()->id, ['issue_id' => $issue->id]);

        return redirect()->route('editor.issues.index')->with('status', 'Proceedings updated.');
    }

    private function form(Issue $issue): View
    {
        return view('editor.issues.form', [
            'issue' => $issue,
            'images' => Media::where('kind', Media::KIND_IMAGE)->orderBy('original_name')->get(['id', 'original_name']),
        ]);
    }

    /**
     * An image uploaded on the form goes into the Media Library and becomes the proceedings cover.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withCover(Request $request, array $data): array
    {
        unset($data['cover_image'], $data['cover_alt']);

        if ($request->hasFile('cover_image')) {
            $cover = $this->uploader->store($request->file('cover_image'), $request->input('cover_alt'), null, $request->user());
            $data['cover_media_id'] = $cover->id;
        }

        return $data;
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
            'cover_image' => ['nullable', 'file', 'max:'.intdiv((int) config('media.max_image_bytes'), 1024)],
            'cover_alt' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $request->hasFile('cover_image'))],
            'cover_media_id' => ['nullable', 'integer', Rule::exists(Media::class, 'id')->where('kind', Media::KIND_IMAGE)],
        ]);

        $published = (bool) ($data['published'] ?? false);
        unset($data['published']);

        return $data + ['published_at' => $published ? ($issue?->published_at ?? now()) : null];
    }
}
