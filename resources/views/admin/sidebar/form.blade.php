@php
    $selectClass = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $kindLabels = [
        \App\Models\SidebarSection::KIND_LINKS => 'Links (one "Label | address" per line)',
        \App\Models\SidebarSection::KIND_TEXT => 'Text (Markdown)',
        \App\Models\SidebarSection::KIND_VISITOR => 'Visitor statistics (automatic)',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $section->exists ? 'Edit Section' : 'New Section' }}</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <form method="POST" action="{{ $section->exists ? route('admin.sidebar.update', $section) : route('admin.sidebar.store') }}" class="space-y-4">
                @csrf
                @if ($section->exists) @method('PUT') @endif

                <x-field name="title" label="Title (shown in capitals, for example QUICK MENU)" :value="$section->title" required />

                <div>
                    <x-input-label for="kind" value="Type" />
                    <select id="kind" name="kind" class="{{ $selectClass }}">
                        @foreach ($kindLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('kind', $section->kind) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('kind')" />
                </div>

                <div>
                    <x-input-label for="body" value="Content (not used by Visitor statistics)" />
                    <x-text-area id="body" name="body" rows="8" maxlength="5000">{{ old('body', $section->body) }}</x-text-area>
                    <x-input-error class="mt-2" :messages="$errors->get('body')" />
                </div>

                <div>
                    <x-input-label for="sort_order" value="Order (smaller numbers appear first)" />
                    <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="9999" class="mt-1 block w-full" :value="old('sort_order', $section->sort_order ?? 0)" />
                    <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                </div>

                <x-checkbox-field name="is_visible" label="Show in the sidebar" :checked="(bool) old('is_visible', $section->is_visible)" />

                <x-primary-button>Save</x-primary-button>
            </form>
        </x-card>
    </div>
</x-app-layout>
