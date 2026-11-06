<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SidebarSectionRequest;
use App\Models\AuditLog;
use App\Models\SidebarSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SidebarSectionController extends Controller
{
    public function index(): View
    {
        return view('admin.sidebar.index', [
            'sections' => SidebarSection::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new SidebarSection(['kind' => SidebarSection::KIND_LINKS, 'sort_order' => 10, 'is_visible' => true]));
    }

    public function store(SidebarSectionRequest $request): RedirectResponse
    {
        $section = SidebarSection::create($request->validated());

        AuditLog::record('sidebar.created', $request->user()->id, ['sidebar_section_id' => $section->id]);

        return redirect()->route('admin.sidebar.index')->with('status', 'Sidebar section saved.');
    }

    public function edit(SidebarSection $sidebarSection): View
    {
        return $this->form($sidebarSection);
    }

    public function update(SidebarSectionRequest $request, SidebarSection $sidebarSection): RedirectResponse
    {
        $sidebarSection->update($request->validated());

        AuditLog::record('sidebar.updated', $request->user()->id, ['sidebar_section_id' => $sidebarSection->id]);

        return redirect()->route('admin.sidebar.index')->with('status', 'Sidebar section saved.');
    }

    public function destroy(SidebarSection $sidebarSection): RedirectResponse
    {
        $id = $sidebarSection->id;
        $sidebarSection->delete();

        AuditLog::record('sidebar.deleted', request()->user()->id, ['sidebar_section_id' => $id]);

        return redirect()->route('admin.sidebar.index')->with('status', 'Sidebar section deleted.');
    }

    private function form(SidebarSection $section): View
    {
        return view('admin.sidebar.form', ['section' => $section]);
    }
}
