<h2 class="text-lg font-medium text-gray-900">Roles</h2>
<p class="mt-1 text-sm text-gray-600">The roles this account has. Only an admin can change them.</p>

<ul class="mt-6 divide-y divide-gray-200 rounded-md border border-gray-200">
    @forelse ($user->roles as $assigned)
        <li class="px-4 py-3 text-sm font-medium text-gray-800">{{ $assigned->role->label() }}</li>
    @empty
        <li class="px-4 py-3 text-sm text-gray-600">No roles assigned.</li>
    @endforelse
</ul>
