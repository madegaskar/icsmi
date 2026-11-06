<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $member->exists ? 'Edit Person' : 'Add Person' }}</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <form method="POST" action="{{ $member->exists ? route('admin.board.update', $member) : route('admin.board.store') }}" class="space-y-4">
                @csrf
                @if ($member->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="group" value="List" />
                    <select id="group" name="group" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="editorial" @selected(old('group', $member->group) === 'editorial')>Editorial Board</option>
                        <option value="speaker" @selected(old('group', $member->group) === 'speaker')>Speakers</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('group')" />
                </div>

                <x-field name="name" label="Name" :value="$member->name" required />
                <x-field name="position" label="Position" :value="$member->position" />
                <x-field name="affiliation" label="Affiliation" :value="$member->affiliation" />
                <x-field name="country" label="Country" :value="$member->country" />
                <x-field name="sort_order" label="Display order" type="number" :value="$member->sort_order" />

                <x-primary-button>Save</x-primary-button>
            </form>
        </x-card>
    </div>
</x-app-layout>
