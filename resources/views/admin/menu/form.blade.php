@php
    $selectClass = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $typeLabels = [
        \App\Models\MenuItem::TYPE_INTERNAL => 'Page on this site',
        \App\Models\MenuItem::TYPE_EXTERNAL => 'External link (https:// or mailto:)',
        \App\Models\MenuItem::TYPE_MEDIA => 'File from the library',
        \App\Models\MenuItem::TYPE_GROUP => 'Group heading (footer only, no link)',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $item->exists ? 'Edit Menu Item' : 'New Menu Item' }}</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <form method="POST" action="{{ $item->exists ? route('admin.menu.update', $item) : route('admin.menu.store') }}" class="space-y-4">
                @csrf
                @if ($item->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="location" value="Menu" />
                    <select id="location" name="location" class="{{ $selectClass }}">
                        @foreach (['header' => 'Header', 'footer' => 'Footer'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('location', $item->location) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('location')" />
                </div>

                <div>
                    <x-input-label for="label" value="Label" />
                    <x-text-input id="label" name="label" type="text" maxlength="100" class="mt-1 block w-full" :value="old('label', $item->label)" />
                    <x-input-error class="mt-2" :messages="$errors->get('label')" />
                </div>

                <div>
                    <x-input-label for="link_type" value="Link type" />
                    <select id="link_type" name="link_type" class="{{ $selectClass }}">
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('link_type', $item->link_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('link_type')" />
                </div>

                <div>
                    <x-input-label for="url" value="Address (/page, https://..., or mailto:...)" />
                    <x-text-input id="url" name="url" type="text" maxlength="255" class="mt-1 block w-full" :value="old('url', $item->url)" />
                    <x-input-error class="mt-2" :messages="$errors->get('url')" />
                </div>

                <div>
                    <x-input-label for="media_id" value="File (only if the link type is File from the library)" />
                    <select id="media_id" name="media_id" class="{{ $selectClass }}">
                        <option value="">(none)</option>
                        @foreach ($mediaOptions as $media)
                            <option value="{{ $media->id }}" @selected((string) old('media_id', $item->media_id) === (string) $media->id)>{{ $media->original_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('media_id')" />
                </div>

                <div>
                    <x-input-label for="parent_id" value="Submenu of (leave empty for a top-level item)" />
                    <select id="parent_id" name="parent_id" class="{{ $selectClass }}">
                        <option value="">(none)</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" @selected((string) old('parent_id', $item->parent_id) === (string) $parent->id)>{{ ucfirst($parent->location) }}: {{ $parent->label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
                </div>

                <div>
                    <x-input-label for="sort_order" value="Order (smaller numbers appear first)" />
                    <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="9999" class="mt-1 block w-full" :value="old('sort_order', $item->sort_order ?? 0)" />
                    <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                </div>

                <x-checkbox-field name="is_visible" label="Show on the website" :checked="(bool) old('is_visible', $item->is_visible)" />

                <x-primary-button>Save</x-primary-button>
            </form>
        </x-card>
    </div>
</x-app-layout>
