<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate(['action' => ['nullable', 'string', 'max:50']]);

        return view('admin.audit-log', [
            'logs' => AuditLog::with('user:id,name,email')
                ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
                ->latest('id')
                ->paginate(50)
                ->withQueryString(),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'action' => $filters['action'] ?? null,
        ]);
    }
}
