@php
    $selectClass = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $typeLabels = [
        \App\Models\HomeBlock::TYPE_HERO => 'Hero banner (only one allowed)',
        \App\Models\HomeBlock::TYPE_PROCEEDINGS => 'Latest proceedings',
        \App\Models\HomeBlock::TYPE_ANNOUNCEMENTS => 'Latest announcements',
        \App\Models\HomeBlock::TYPE_TEXT => 'Free text',
        \App\Models\HomeBlock::TYPE_TABLE => 'Information table (one "Label | Value" per line)',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $block->exists ? 'Edit Block' : 'New Block' }}</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <form method="POST" action="{{ $block->exists ? route('admin.home-blocks.update', $block) : route('admin.home-blocks.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @if ($block->exists) @method('PUT') @endif

                <div>
                    <x-input-label for="type" value="Block type" />
                    <select id="type" name="type" class="{{ $selectClass }}">
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $block->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('type')" />
                </div>

                <div>
                    <x-input-label for="sort_order" value="Order (smaller numbers appear first)" />
                    <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="9999" class="mt-1 block w-full" :value="old('sort_order', $block->sort_order ?? 0)" />
                    <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="starts_at" value="Show from (optional)" />
                        <input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('starts_at', $block->starts_at?->format('Y-m-d\TH:i')) }}">
                        <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                    </div>
                    <div>
                        <x-input-label for="ends_at" value="Show until (optional)" />
                        <input id="ends_at" name="ends_at" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('ends_at', $block->ends_at?->format('Y-m-d\TH:i')) }}">
                        <x-input-error class="mt-2" :messages="$errors->get('ends_at')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="heading" value="Heading" />
                    <x-text-input id="heading" name="heading" type="text" maxlength="150" class="mt-1 block w-full" :value="old('heading', $block->heading)" />
                    <x-input-error class="mt-2" :messages="$errors->get('heading')" />
                </div>

                <div>
                    <x-input-label for="subtitle" value="Subtitle (hero only)" />
                    <x-text-input id="subtitle" name="subtitle" type="text" maxlength="255" class="mt-1 block w-full" :value="old('subtitle', $block->subtitle)" />
                    <x-input-error class="mt-2" :messages="$errors->get('subtitle')" />
                </div>

                <div>
                    <x-input-label for="body" value="Text (free text: Markdown; information table: one \"Label | Value\" per line)" />
                    <x-text-area id="body" name="body" rows="8" maxlength="10000">{{ old('body', $block->body) }}</x-text-area>
                    <x-input-error class="mt-2" :messages="$errors->get('body')" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="button_label" value="Button label (hero only)" />
                        <x-text-input id="button_label" name="button_label" type="text" maxlength="60" class="mt-1 block w-full" :value="old('button_label', $block->button_label)" />
                        <x-input-error class="mt-2" :messages="$errors->get('button_label')" />
                    </div>
                    <div>
                        <x-input-label for="button_url" value="Button address (/page or https://...)" />
                        <x-text-input id="button_url" name="button_url" type="text" maxlength="255" class="mt-1 block w-full" :value="old('button_url', $block->button_url)" />
                        <x-input-error class="mt-2" :messages="$errors->get('button_url')" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="desktop_media_id" value="Desktop image (hero, 1920 × 800 px recommended)" />
                        <select id="desktop_media_id" name="desktop_media_id" class="{{ $selectClass }}">
                            <option value="">—</option>
                            @foreach ($images as $image)
                                <option value="{{ $image->id }}" @selected((string) old('desktop_media_id', $block->desktop_media_id) === (string) $image->id)>{{ $image->original_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('desktop_media_id')" />
                    </div>
                    <div>
                        <x-input-label for="mobile_media_id" value="Phone image (hero, 750 × 900 px recommended)" />
                        <select id="mobile_media_id" name="mobile_media_id" class="{{ $selectClass }}">
                            <option value="">—</option>
                            @foreach ($images as $image)
                                <option value="{{ $image->id }}" @selected((string) old('mobile_media_id', $block->mobile_media_id) === (string) $image->id)>{{ $image->original_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('mobile_media_id')" />
                    </div>
                </div>

                <div class="rounded-md border border-dashed border-gray-300 p-4">
                    <x-input-label for="desktop_image" value="Upload a new hero image (JPG, PNG, or WebP, up to 5 MB; it is added to the Media Library)" />
                    <input id="desktop_image" name="desktop_image" type="file" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full text-sm text-gray-700">
                    <x-input-error class="mt-2" :messages="$errors->get('desktop_image')" />
                    <div class="mt-3">
                        <x-input-label for="image_alt" value="Alt text for the uploaded image (required with an upload)" />
                        <x-text-input id="image_alt" name="image_alt" type="text" maxlength="255" class="mt-1 block w-full" :value="old('image_alt')" />
                        <x-input-error class="mt-2" :messages="$errors->get('image_alt')" />
                    </div>
                </div>

                <div class="rounded-md border border-dashed border-gray-300 p-4">
                    <x-input-label for="cover_image" value="Cover image beside the table (JPG, PNG, or WebP, up to 5 MB; it is added to the Media Library)" />
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
                                <option value="{{ $image->id }}" @selected((string) old('cover_media_id', $block->cover_media_id) === (string) $image->id)>{{ $image->original_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('cover_media_id')" />
                    </div>
                </div>

                <x-checkbox-field name="is_visible" label="Show on the home page" :checked="(bool) old('is_visible', $block->is_visible)" />

                <x-primary-button>Save</x-primary-button>
            </form>
        </x-card>
    </div>
</x-app-layout>
