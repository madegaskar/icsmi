<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $issue->exists ? 'Edit Proceedings' : 'New Proceedings' }}</h2>
        @include('editor.partials.tabs')
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <form method="POST" action="{{ $issue->exists ? route('editor.issues.update', $issue) : route('editor.issues.store') }}" class="grid gap-4 sm:grid-cols-2" enctype="multipart/form-data">
                @csrf
                @if ($issue->exists) @method('PUT') @endif

                <x-field name="title" label="Title" :value="$issue->title" required class="sm:col-span-2" />
                <x-field name="year" label="Year" type="number" :value="$issue->year" required />
                <x-field name="volume" label="Volume (optional)" type="number" :value="$issue->volume" />
                <x-field name="number" label="Number (optional)" type="number" :value="$issue->number" />
                <x-field name="conference_dates" label="Conference dates" :value="$issue->conference_dates" />
                <x-field name="location" label="Location" :value="$issue->location" />
                <x-field name="isbn" label="ISBN" :value="$issue->isbn" />
                <x-field name="issn" label="ISSN" :value="$issue->issn" />

                <div class="sm:col-span-2">
                    <x-input-label for="description" value="Description" />
                    <x-text-area id="description" name="description" maxlength="5000">{{ old('description', $issue->description) }}</x-text-area>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="rounded-md border border-dashed border-gray-300 p-4 sm:col-span-2">
                    <x-input-label for="cover_image" value="Cover image shown beside the proceedings in the archive (JPG, PNG, or WebP, up to 5 MB; it is added to the Media Library)" />
                    <input id="cover_image" name="cover_image" type="file" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full text-sm text-gray-700">
                    <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
                    <div class="mt-3">
                        <x-input-label for="cover_alt" value="Alt text for the cover (required with an upload)" />
                        <x-text-input id="cover_alt" name="cover_alt" type="text" maxlength="255" class="mt-1 block w-full" :value="old('cover_alt')" />
                        <x-input-error class="mt-2" :messages="$errors->get('cover_alt')" />
                    </div>
                    <div class="mt-3">
                        <x-input-label for="cover_media_id" value="Or pick an existing image from the library" />
                        <select id="cover_media_id" name="cover_media_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">—</option>
                            @foreach ($images as $image)
                                <option value="{{ $image->id }}" @selected((string) old('cover_media_id', $issue->cover_media_id) === (string) $image->id)>{{ $image->original_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('cover_media_id')" />
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <x-checkbox-field name="published" label="Publish on the website" :checked="(bool) $issue->published_at" />
                </div>

                <div class="sm:col-span-2"><x-primary-button>Save</x-primary-button></div>
            </form>
        </x-card>
    </div>
</x-app-layout>
