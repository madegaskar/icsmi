<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageRevision;
use App\Rules\PageSlug;
use App\Services\PageEditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    private const MAX_BODY_LENGTH = 50000;

    public function __construct(private readonly PageEditor $editor) {}

    public function index(): View
    {
        return view('admin.pages.index', ['pages' => Page::orderBy('title')->get()]);
    }

    public function create(): View
    {
        return view('admin.pages.form', [
            'page' => new Page,
            'images' => $this->images(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = $this->editor->create($this->validated($request), $request->user());

        return redirect()->route('admin.pages.index')->with('status', "Page saved as {$this->stateLabel($page)}.");
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', [
            'page' => $page,
            'images' => $this->images(),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page = $this->editor->update($page, $this->validated($request, $page), $request->user());

        return redirect()->route('admin.pages.index')->with('status', "Page saved as {$this->stateLabel($page)}.");
    }

    public function destroy(Request $request, Page $page): RedirectResponse
    {
        $this->editor->delete($page, $request->user());

        return redirect()->route('admin.pages.index')->with('status', 'Page deleted. Its history is kept.');
    }

    public function preview(Page $page): View
    {
        return view('site.page', ['page' => $page, 'preview' => true]);
    }

    public function revisions(Page $page): View
    {
        return view('admin.pages.revisions', [
            'page' => $page,
            'revisions' => $page->revisions()->with('author:id,name')->paginate(20),
        ]);
    }

    public function restore(Request $request, Page $page, PageRevision $revision): RedirectResponse
    {
        abort_unless($revision->page_id === $page->id, 404);

        $this->editor->restore($page, $revision, $request->user());

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Revision restored. The earlier version is still in the history.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:200', new PageSlug, Rule::unique('pages', 'slug')->ignore($page?->id)],
            'body' => ['required', 'string', 'max:'.self::MAX_BODY_LENGTH],
            'visibility' => ['nullable', Rule::in([PageEditor::VISIBILITY_DRAFT, PageEditor::VISIBILITY_PUBLISH])],
            'publish_at' => ['nullable', 'date'],
        ]);
    }

    private function stateLabel(Page $page): string
    {
        return match ($page->state()) {
            Page::STATE_PUBLISHED => 'published',
            Page::STATE_SCHEDULED => 'scheduled',
            default => 'draft',
        };
    }

    /**
     * Library images, so editors can insert them into a page as Markdown.
     */
    private function images()
    {
        return Media::where('kind', Media::KIND_IMAGE)->orderBy('original_name')->get(['uuid', 'original_name', 'alt_text']);
    }
}
