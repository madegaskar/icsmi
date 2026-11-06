<x-public-layout title="Search">
    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold">Search Articles</h1>

        <form method="GET" action="{{ route('search') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="q" value="Title or abstract" />
                <x-text-input id="q" name="q" type="search" class="mt-1 block w-full" maxlength="200" :value="$filters['q'] ?? ''" />
                <x-input-error class="mt-2" :messages="$errors->get('q')" />
            </div>
            <div>
                <x-input-label for="author" value="Author" />
                <x-text-input id="author" name="author" type="search" class="mt-1 block w-full" maxlength="200" :value="$filters['author'] ?? ''" />
                <x-input-error class="mt-2" :messages="$errors->get('author')" />
            </div>
            <div>
                <x-input-label for="keyword" value="Keyword" />
                <x-text-input id="keyword" name="keyword" type="search" class="mt-1 block w-full" maxlength="200" :value="$filters['keyword'] ?? ''" />
                <x-input-error class="mt-2" :messages="$errors->get('keyword')" />
            </div>
            <div>
                <x-input-label for="year" value="Year" />
                <x-text-input id="year" name="year" type="number" class="mt-1 block w-full" min="1900" max="2100" :value="$filters['year'] ?? ''" />
                <x-input-error class="mt-2" :messages="$errors->get('year')" />
            </div>
            <div class="sm:col-span-2">
                <x-primary-button>Search</x-primary-button>
            </div>
        </form>

        @if ($hasSearched)
            <p class="mt-8 text-sm text-gray-600">{{ $articles->total() }} {{ Str::plural('result', $articles->total()) }}</p>
        @endif

        <ul class="mt-2 divide-y divide-gray-200">
            @if ($hasSearched)
                @forelse ($articles as $article)
                    @include('site.partials.article-row')
                @empty
                    <li class="py-4 text-gray-600">No articles match your search.</li>
                @endforelse
            @endif
        </ul>

        @if ($hasSearched)
            <div class="mt-6">{{ $articles->links() }}</div>
        @endif
    </div>
</x-public-layout>
