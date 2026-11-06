@php
    $selectClass = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $announcement->exists ? 'Edit Announcement' : 'New Announcement' }}</h2>
        @include('admin.pages.tabs')
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <form method="POST" action="{{ $announcement->exists ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" class="space-y-4">
                @csrf
                @if ($announcement->exists) @method('PUT') @endif

                <x-field name="title" label="Title (5 to 150 characters)" :value="$announcement->title" required />

                <div>
                    <x-input-label for="category" value="Category" />
                    <select id="category" name="category" class="{{ $selectClass }}">
                        @foreach (\App\Models\Announcement::CATEGORIES as $value)
                            <option value="{{ $value }}" @selected(old('category', $announcement->category ?? 'news') === $value)>{{ \App\Models\Announcement::labelFor($value) }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('category')" />
                </div>

                <div>
                    <x-input-label for="body" value="Text (10 to 10,000 characters)" />
                    <x-text-area id="body" name="body" rows="8" maxlength="10000" required>{{ old('body', $announcement->body) }}</x-text-area>
                    <x-input-error class="mt-2" :messages="$errors->get('body')" />
                </div>

                <div>
                    <x-input-label for="ends_at" value="Hide after (optional)" />
                    <input id="ends_at" name="ends_at" type="datetime-local" class="{{ $selectClass }}" value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d\TH:i')) }}">
                    <x-input-error class="mt-2" :messages="$errors->get('ends_at')" />
                </div>

                <x-checkbox-field name="published" label="Publish on the website" :checked="(bool) $announcement->published_at" />

                <x-primary-button>Save</x-primary-button>
            </form>
        </x-card>
    </div>
</x-app-layout>
