@php
    $selectClass = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $visibility = old('visibility', $page->exists ? ($page->published_at ? 'publish' : 'draft') : 'draft');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $page->exists ? 'Edit Page' : 'New Page' }}</h2>
            @if ($page->exists)
                <div class="flex gap-4 text-sm">
                    <a class="text-gray-600 underline hover:text-gray-900" href="{{ route('admin.pages.preview', $page) }}">Preview</a>
                    <a class="text-gray-600 underline hover:text-gray-900" href="{{ route('admin.pages.revisions', $page) }}">History</a>
                </div>
            @endif
        </div>
        @include('admin.pages.tabs')
    </x-slot>

    <div class="mx-auto grid max-w-6xl gap-8 py-8 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div class="lg:col-span-2">
            <x-card>
                <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="space-y-4">
                    @csrf
                    @if ($page->exists) @method('PUT') @endif

                    <x-field name="title" label="Title" :value="$page->title" required />

                    <div>
                        <x-input-label for="slug" value="Address (leave empty to create it from the title)" />
                        <div class="mt-1 flex items-center gap-1 text-sm text-gray-600">
                            <span>/</span>
                            <x-text-input id="slug" name="slug" type="text" maxlength="200" class="block w-full" :value="old('slug', $page->slug)" />
                        </div>
                        @if ($page->exists)
                            <p class="mt-1 text-xs text-gray-500">Changing the address breaks links that point to the old one.</p>
                        @endif
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>

                    <div>
                        <x-input-label for="body" value="Content (Markdown; HTML is removed)" />
                        <x-text-area id="body" name="body" rows="18" maxlength="50000" required>{{ old('body', $page->body) }}</x-text-area>
                        <x-input-error class="mt-2" :messages="$errors->get('body')" />
                    </div>

                    <fieldset class="space-y-2">
                        <legend class="text-sm font-medium text-gray-700">Visibility</legend>
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="visibility" value="draft" @checked($visibility === 'draft')> Draft (not visible to visitors)</label>
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="visibility" value="publish" @checked($visibility === 'publish')> Publish</label>
                        <x-input-error class="mt-2" :messages="$errors->get('visibility')" />
                    </fieldset>

                    <div>
                        <x-input-label for="publish_at" value="Publish at (optional; empty means now; a future time schedules the page)" />
                        <input id="publish_at" name="publish_at" type="datetime-local" class="{{ $selectClass }}" value="{{ old('publish_at', $page->published_at?->format('Y-m-d\TH:i')) }}">
                        <x-input-error class="mt-2" :messages="$errors->get('publish_at')" />
                    </div>

                    <x-primary-button>Save</x-primary-button>
                </form>
            </x-card>
        </div>

        <aside>
            <x-card>
                <h3 class="text-sm font-semibold text-gray-800">Insert an image</h3>
                <p class="mt-1 text-xs text-gray-600">Copy a line below into the content. Images come from the Media Library.</p>

                <ul class="mt-3 space-y-3 text-xs">
                    @forelse ($images as $image)
                        <li>
                            <code class="block break-all rounded bg-gray-100 px-2 py-1">![{{ $image->alt_text }}](/media/{{ $image->uuid }}/desktop)</code>
                        </li>
                    @empty
                        <li class="text-gray-600">No images yet. Upload one in the <a class="text-emerald-800 underline" href="{{ route('library.index') }}">Media Library</a>.</li>
                    @endforelse
                </ul>
            </x-card>
        </aside>
    </div>
</x-app-layout>
