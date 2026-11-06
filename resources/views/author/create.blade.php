<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Submit Manuscript</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <form method="POST" action="{{ route('author.manuscripts.store') }}" enctype="multipart/form-data">
                @csrf

                <div>
                    <x-input-label for="title" value="Title" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" maxlength="255" :value="old('title')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <div class="mt-4">
                    <x-input-label for="abstract" value="Abstract" />
                    <textarea id="abstract" name="abstract" rows="8" maxlength="5000" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('abstract') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('abstract')" />
                </div>

                <div class="mt-4">
                    <x-input-label for="keywords" value="Keywords (comma separated)" />
                    <x-text-input id="keywords" name="keywords" type="text" class="mt-1 block w-full" maxlength="500" :value="old('keywords')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('keywords')" />
                </div>

                <h3 class="mt-8 font-medium text-gray-900">Authors</h3>
                <p class="mt-1 text-xs text-gray-500">Leave unused rows empty. The first author is the corresponding author.</p>
                <x-input-error class="mt-2" :messages="$errors->get('authors')" />

                @for ($i = 0; $i < $maxAuthors; $i++)
                    <fieldset class="mt-4 grid gap-3 sm:grid-cols-3">
                        <legend class="mb-1 text-sm font-medium text-gray-700">Author {{ $i + 1 }}</legend>
                        <div>
                            <x-text-input name="authors[{{ $i }}][name]" type="text" class="block w-full" placeholder="Name" aria-label="Author {{ $i + 1 }} name" :value="old('authors.'.$i.'.name', $i === 0 ? $user->name : '')" />
                            <x-input-error class="mt-1" :messages="$errors->get('authors.'.$i.'.name')" />
                        </div>
                        <div>
                            <x-text-input name="authors[{{ $i }}][email]" type="email" class="block w-full" placeholder="Email" aria-label="Author {{ $i + 1 }} email" :value="old('authors.'.$i.'.email', $i === 0 ? $user->email : '')" />
                            <x-input-error class="mt-1" :messages="$errors->get('authors.'.$i.'.email')" />
                        </div>
                        <div>
                            <x-text-input name="authors[{{ $i }}][affiliation]" type="text" class="block w-full" placeholder="Affiliation" aria-label="Author {{ $i + 1 }} affiliation" :value="old('authors.'.$i.'.affiliation', $i === 0 ? $user->affiliation : '')" />
                            <x-input-error class="mt-1" :messages="$errors->get('authors.'.$i.'.affiliation')" />
                        </div>
                    </fieldset>
                @endfor

                <h3 class="mt-8 font-medium text-gray-900">Files</h3>
                @include('manuscripts.upload-fields')

                <div class="mt-6">
                    <x-primary-button>Submit</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
