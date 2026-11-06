<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $media->exists ? 'Edit File' : 'Upload File' }}</h2>
        @include('editor.partials.tabs')
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <form method="POST" enctype="multipart/form-data"
                  action="{{ $media->exists ? route('library.update', $media) : route('library.store') }}" class="space-y-4">
                @csrf
                @if ($media->exists) @method('PUT') @endif

                @unless ($media->exists)
                    <div>
                        <x-input-label for="file" value="File (JPG, PNG, or WebP up to 5 MB; PDF up to 20 MB)" />
                        <input id="file" name="file" type="file" required accept=".jpg,.jpeg,.png,.webp,.pdf" class="mt-1 block w-full text-sm text-gray-700">
                        <x-input-error class="mt-2" :messages="$errors->get('file')" />
                    </div>
                @else
                    <p class="text-sm text-gray-600">File: {{ $media->original_name }} ({{ $media->isImage() ? 'image' : 'PDF' }})</p>
                @endunless

                <div>
                    <x-input-label for="alt_text" value="Alt text (required for images)" />
                    <x-text-input id="alt_text" name="alt_text" type="text" maxlength="255" class="mt-1 block w-full" :value="old('alt_text', $media->alt_text)" />
                    <x-input-error class="mt-2" :messages="$errors->get('alt_text')" />
                </div>

                <div>
                    <x-input-label for="caption" value="Caption" />
                    <x-text-input id="caption" name="caption" type="text" maxlength="500" class="mt-1 block w-full" :value="old('caption', $media->caption)" />
                    <x-input-error class="mt-2" :messages="$errors->get('caption')" />
                </div>

                <x-primary-button>{{ $media->exists ? 'Save' : 'Upload' }}</x-primary-button>
            </form>
        </x-card>
    </div>
</x-app-layout>
