<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\AuditLog;
use App\Models\MenuItem;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        return view('admin.menu.index', [
            'items' => MenuItem::with('media')
                ->orderBy('location')
                ->orderByRaw('COALESCE(parent_id, id)')
                ->orderBy('parent_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new MenuItem(['location' => MenuItem::LOCATION_HEADER, 'link_type' => MenuItem::TYPE_INTERNAL, 'is_visible' => true]));
    }

    public function store(MenuItemRequest $request): RedirectResponse
    {
        $item = MenuItem::create($request->validated());

        AuditLog::record('menu.created', $request->user()->id, ['menu_item_id' => $item->id]);

        return redirect()->route('admin.menu.index')->with('status', 'Menu item saved.');
    }

    public function edit(MenuItem $menuItem): View
    {
        return $this->form($menuItem);
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update($request->validated());

        AuditLog::record('menu.updated', $request->user()->id, ['menu_item_id' => $menuItem->id]);

        return redirect()->route('admin.menu.index')->with('status', 'Menu item saved.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $id = $menuItem->id;
        $menuItem->delete();

        AuditLog::record('menu.deleted', request()->user()->id, ['menu_item_id' => $id]);

        return redirect()->route('admin.menu.index')->with('status', 'Menu item deleted.');
    }

    private function form(MenuItem $item): View
    {
        return view('admin.menu.form', [
            'item' => $item,
            'parents' => MenuItem::whereNull('parent_id')
                ->when($item->exists, fn ($query) => $query->whereKeyNot($item->id))
                ->orderBy('location')
                ->orderBy('sort_order')
                ->get(),
            'mediaOptions' => Media::orderBy('original_name')->get(['id', 'original_name', 'kind']),
        ]);
    }
}
