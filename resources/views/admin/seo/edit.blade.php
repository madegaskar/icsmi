<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">SEO</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-card>
            <form method="POST" action="{{ route('admin.seo.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="seo_description" value="Default meta description (up to 300 characters)" />
                    <p class="mt-1 text-sm text-gray-600">Shown to search engines on pages that have no description of their own.</p>
                    <x-text-area id="seo_description" name="seo_description" maxlength="300" class="mt-2">{{ old('seo_description', $description) }}</x-text-area>
                    <x-input-error class="mt-2" :messages="$errors->get('seo_description')" />
                </div>

                <div>
                    <x-input-label for="seo_keywords" value="Meta keywords (comma separated, up to 255 characters)" />
                    <x-text-input id="seo_keywords" name="seo_keywords" type="text" maxlength="255" class="mt-2 block w-full" :value="old('seo_keywords', $keywords)" />
                    <x-input-error class="mt-2" :messages="$errors->get('seo_keywords')" />
                </div>

                <x-primary-button>Save</x-primary-button>
            </form>
        </x-card>
    </div>
</x-app-layout>
