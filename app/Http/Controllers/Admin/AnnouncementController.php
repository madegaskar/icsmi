<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Rules\NoControlCharacters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', ['announcements' => Announcement::latest('id')->paginate(25)]);
    }

    public function create(): View
    {
        return view('admin.announcements.form', ['announcement' => new Announcement]);
    }

    public function store(Request $request): RedirectResponse
    {
        $announcement = Announcement::create($this->validated($request));

        AuditLog::record('announcement.created', $request->user()->id, ['announcement_id' => $announcement->id]);

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement saved.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.form', ['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validated($request, $announcement));

        AuditLog::record('announcement.updated', $request->user()->id, ['announcement_id' => $announcement->id]);

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement saved.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        AuditLog::record('announcement.deleted', $request->user()->id, ['announcement_id' => $announcement->id]);

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Announcement $announcement = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:150', new NoControlCharacters],
            'body' => ['required', 'string', 'min:10', 'max:10000', new NoControlCharacters],
            'category' => ['required', Rule::in(Announcement::CATEGORIES)],
            'ends_at' => ['nullable', 'date', 'after:now'],
            'published' => ['nullable', 'boolean'],
        ]);

        $published = (bool) ($data['published'] ?? false);
        unset($data['published']);

        return $data + ['published_at' => $published ? ($announcement?->published_at ?? now()) : null];
    }
}
