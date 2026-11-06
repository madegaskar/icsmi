<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BoardMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BoardMemberController extends Controller
{
    public function index(): View
    {
        return view('admin.board.index', ['members' => BoardMember::orderBy('group')->orderBy('sort_order')->paginate(50)]);
    }

    public function create(): View
    {
        return view('admin.board.form', ['member' => new BoardMember]);
    }

    public function store(Request $request): RedirectResponse
    {
        $member = BoardMember::create($this->validated($request));

        AuditLog::record('board_member.created', $request->user()->id, ['board_member_id' => $member->id]);

        return redirect()->route('admin.board.index')->with('status', 'Member saved.');
    }

    public function edit(BoardMember $board): View
    {
        return view('admin.board.form', ['member' => $board]);
    }

    public function update(Request $request, BoardMember $board): RedirectResponse
    {
        $board->update($this->validated($request));

        AuditLog::record('board_member.updated', $request->user()->id, ['board_member_id' => $board->id]);

        return redirect()->route('admin.board.index')->with('status', 'Member saved.');
    }

    public function destroy(Request $request, BoardMember $board): RedirectResponse
    {
        $board->delete();

        AuditLog::record('board_member.deleted', $request->user()->id, ['board_member_id' => $board->id]);

        return redirect()->route('admin.board.index')->with('status', 'Member deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'group' => ['required', Rule::in([BoardMember::GROUP_EDITORIAL, BoardMember::GROUP_SPEAKER])],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + ['sort_order' => 0];
    }
}
