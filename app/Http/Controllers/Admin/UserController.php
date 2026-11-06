<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $term = $filters['q'] ?? null;

        return view('admin.users.index', [
            'users' => User::with('roles')
                ->when($term, function ($query, $term) {
                    $pattern = '%'.strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
                    $query->where(fn ($query) => $query->whereRaw("name LIKE ? ESCAPE '!'", [$pattern])->orWhereRaw("email LIKE ? ESCAPE '!'", [$pattern]));
                })
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),
            'q' => $term,
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user->load('roles')]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::enum(Role::class)],
        ]);

        $roles = array_values(array_unique($validated['roles'] ?? []));

        DB::transaction(function () use ($user, $roles) {
            if ($user->hasRole(Role::Admin) && ! in_array(Role::Admin->value, $roles, true)
                && UserRole::where('role', Role::Admin)->count() === 1) {
                throw ValidationException::withMessages(['roles' => 'At least one administrator must remain.']);
            }

            $user->roles()->delete();

            foreach ($roles as $role) {
                $user->roles()->create(['role' => $role]);
            }
        });

        AuditLog::record('user.roles_changed', $request->user()->id, ['user_id' => $user->id, 'roles' => $roles]);

        return redirect()->route('admin.users.index')->with('status', 'Roles updated.');
    }
}
