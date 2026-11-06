<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3 px-4 sm:px-0">
            <div>
                <x-input-label for="q" value="Search name or email" />
                <x-text-input id="q" name="q" type="search" class="mt-1" maxlength="200" :value="$q" />
                <x-input-error class="mt-2" :messages="$errors->get('q')" />
            </div>
            <x-primary-button>Search</x-primary-button>
        </form>

        <x-data-table :headers="['Name', 'Email', 'Affiliation', 'Roles']">
            @forelse ($users as $user)
                <tr>
                    <td class="px-4 py-3"><a class="font-medium hover:text-emerald-800" href="{{ route('admin.users.edit', $user) }}">{{ $user->name }}</a></td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->affiliation }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->roles->map(fn ($assigned) => $assigned->role->label())->join(', ') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-gray-600">No users found.</td></tr>
            @endforelse
        </x-data-table>

        <div>{{ $users->links() }}</div>
    </div>
</x-app-layout>
