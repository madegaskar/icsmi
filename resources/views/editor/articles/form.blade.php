<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $article->exists ? 'Edit Article' : 'Add Existing Article' }}</h2>
        @include('editor.partials.tabs')
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <form method="POST" action="{{ $article->exists ? route('editor.articles.update', $article) : route('editor.articles.store') }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2">
                @csrf
                @if ($article->exists) @method('PUT') @endif

                <div class="sm:col-span-2">
                    <x-input-label for="issue_id" value="Proceedings" />
                    <select id="issue_id" name="issue_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($issues as $issue)
                            <option value="{{ $issue->id }}" @selected((int) old('issue_id', $article->issue_id) === $issue->id)>{{ $issue->title }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('issue_id')" />
                </div>

                <x-field name="title" label="Title" :value="$article->title" required class="sm:col-span-2" />

                <div class="sm:col-span-2">
                    <x-input-label for="abstract" value="Abstract" />
                    <x-text-area id="abstract" name="abstract" rows="8" maxlength="5000" required>{{ old('abstract', $article->abstract) }}</x-text-area>
                    <x-input-error class="mt-2" :messages="$errors->get('abstract')" />
                </div>

                <x-field name="keywords" label="Keywords (comma separated)" :value="$article->exists ? $article->keywords->pluck('name')->join(', ') : null" class="sm:col-span-2" />
                <x-field name="doi" label="DOI" :value="$article->doi" class="sm:col-span-2" />

                <div class="sm:col-span-2">
                    <x-input-label for="reference_list" value="References (one per line)" />
                    <x-text-area id="reference_list" name="reference_list" rows="8" maxlength="20000">{{ old('reference_list', $article->reference_list) }}</x-text-area>
                    <x-input-error class="mt-2" :messages="$errors->get('reference_list')" />
                </div>
                <x-field name="first_page" label="First page" type="number" :value="$article->first_page" />
                <x-field name="last_page" label="Last page" type="number" :value="$article->last_page" />

                <fieldset class="sm:col-span-2">
                    <legend class="text-sm font-medium text-gray-700">Authors</legend>
                    <x-input-error class="mt-1" :messages="$errors->get('authors')" />

                    @for ($i = 0; $i < $maxAuthors; $i++)
                        @php($author = $article->exists ? $article->authors->get($i) : null)
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            <x-text-input name="authors[{{ $i }}][name]" type="text" class="block w-full" placeholder="Name" aria-label="Author {{ $i + 1 }} name" :value="old('authors.'.$i.'.name', $author?->name)" />
                            <x-text-input name="authors[{{ $i }}][affiliation]" type="text" class="block w-full" placeholder="Affiliation" aria-label="Author {{ $i + 1 }} affiliation" :value="old('authors.'.$i.'.affiliation', $author?->affiliation)" />
                        </div>
                    @endfor
                </fieldset>

                <div class="sm:col-span-2">
                    <x-input-label for="pdf" value="{{ $article->pdf_path ? 'Replace PDF (optional, max 20 MB)' : 'PDF (max 20 MB)' }}" />
                    <input id="pdf" name="pdf" type="file" accept=".pdf" class="mt-1 block w-full text-sm text-gray-700">
                    <x-input-error class="mt-2" :messages="$errors->get('pdf')" />
                </div>

                <div class="sm:col-span-2">
                    <x-checkbox-field name="published" label="Publish on the website" :checked="$article->exists ? (bool) $article->published_at : false" />
                    <p class="mt-1 text-xs text-gray-500">The article appears once its proceedings are also published.</p>
                </div>

                <div class="sm:col-span-2"><x-primary-button>Save</x-primary-button></div>
            </form>
        </x-card>
    </div>
</x-app-layout>
