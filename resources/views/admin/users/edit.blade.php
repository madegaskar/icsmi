<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-xl py-8 sm:px-6 lg:px-8">
        <x-card title="Roles">
            <p class="mt-1 text-sm text-gray-600">{{ $user->email }}</p>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-4 space-y-3">
                @csrf
                @method('PUT')

                @foreach (\App\Enums\Role::cases() as $role)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="roles[]" value="{{ $role->value }}" @checked($user->hasRole($role)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        {{ $role->label() }}
                    </label>
                @endforeach

                <x-input-error :messages="$errors->get('roles')" />
                <x-input-error :messages="$errors->get('roles.*')" />

                <x-primary-button>Save Roles</x-primary-button>
            </form>
        </x-card>
    </div>
</x-app-layout>
