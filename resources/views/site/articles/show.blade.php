<x-public-layout :title="$article->title" :description="Str::limit($article->abstract, 160)">
    <x-slot:head>
        <meta name="citation_title" content="{{ $article->title }}">
        @foreach ($article->authors as $author)
            <meta name="citation_author" content="{{ $author->name }}">
            @if ($author->affiliation)
                <meta name="citation_author_institution" content="{{ $author->affiliation }}">
            @endif
        @endforeach
        <meta name="citation_publication_date" content="{{ $article->published_at->format('Y/m/d') }}">
        <meta name="citation_conference_title" content="{{ $article->issue->title }}">
        <meta name="citation_abstract_html_url" content="{{ route('articles.show', $article->slug) }}">
        {{ \App\Support\JsonLd::script([
            '@type' => 'ScholarlyArticle',
            'headline' => $article->title,
            'author' => $article->authors->map(fn ($author) => ['@type' => 'Person', 'name' => $author->name])->values()->all(),
            'datePublished' => $article->published_at->toDateString(),
            'isPartOf' => ['@type' => 'PublicationIssue', 'name' => $article->issue->title],
            'identifier' => $article->doi,
            'url' => route('articles.show', $article->slug),
        ]) }}
        @if ($article->pdf_path)
            <meta name="citation_pdf_url" content="{{ route('articles.pdf', $article->slug) }}">
        @endif
        @if ($article->doi)
            <meta name="citation_doi" content="{{ $article->doi }}">
        @endif
        @if ($article->first_page)
            <meta name="citation_firstpage" content="{{ $article->first_page }}">
        @endif
        @if ($article->last_page)
            <meta name="citation_lastpage" content="{{ $article->last_page }}">
        @endif
        @if ($article->issue->isbn)
            <meta name="citation_isbn" content="{{ $article->issue->isbn }}">
        @endif
        @if ($article->issue->issn)
            <meta name="citation_issn" content="{{ $article->issue->issn }}">
        @endif
        @foreach ($article->keywords as $keyword)
            <meta name="citation_keywords" content="{{ $keyword->name }}">
        @endforeach
    </x-slot:head>

    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <p class="text-sm"><a class="text-emerald-800 hover:underline" href="{{ route('proceedings.show', $article->issue->slug) }}">{{ $article->issue->title }}</a></p>
        <h1 class="mt-2 text-3xl font-bold leading-tight">{{ $article->title }}</h1>

        <ul class="mt-4 space-y-1 text-sm">
            @foreach ($article->authors as $author)
                <li><span class="font-medium">{{ $author->name }}</span>@if ($author->affiliation)<span class="text-gray-600">, {{ $author->affiliation }}</span>@endif</li>
            @endforeach
        </ul>

        <dl class="mt-4 space-y-1 text-sm text-gray-600">
            <div>Published: {{ $article->published_at->format('j F Y') }}</div>
            @if ($article->pages())<div>Pages: {{ $article->pages() }}</div>@endif
            @if ($article->doi)<div>DOI: <a class="text-emerald-800 hover:underline" href="https://doi.org/{{ rawurlencode($article->doi) }}">{{ $article->doi }}</a></div>@endif
        </dl>

        @if ($article->pdf_path)
            <p class="mt-6"><a href="{{ route('articles.pdf', $article->slug) }}" class="inline-block rounded-md bg-emerald-900 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">Download PDF</a></p>
        @endif

        <h2 class="mt-10 text-xl font-semibold">Abstract</h2>
        <p class="mt-3 whitespace-pre-line leading-relaxed text-gray-800">{{ $article->abstract }}</p>

        @if ($article->keywords->isNotEmpty())
            <h2 class="mt-8 text-xl font-semibold">Keywords</h2>
            <ul class="mt-3 flex flex-wrap gap-2 text-sm">
                @foreach ($article->keywords as $keyword)
                    <li><a class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-900 hover:bg-emerald-100" href="{{ route('search', ['keyword' => $keyword->name]) }}">{{ $keyword->name }}</a></li>
                @endforeach
            </ul>
        @endif

        @if ($article->reference_list)
            <h2 class="mt-8 text-xl font-semibold">References</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-gray-800">
                @foreach (preg_split('/\R/', $article->reference_list) as $reference)
                    @continue(trim($reference) === '')
                    <li>{{ trim($reference) }}</li>
                @endforeach
            </ol>
        @endif

        <div class="mt-6 flex flex-wrap gap-x-6 gap-y-1 rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-700">
            <span>Abstract views: <strong>{{ $stats['views'] }}</strong></span>
            <span>PDF Downloads: <strong>{{ $stats['downloads'] }}</strong></span>
        </div>

        <h2 class="mt-8 text-xl font-semibold">How to Cite</h2>
        <h3 class="mt-3 text-sm font-semibold text-gray-700">APA</h3>
        <p class="mt-1 rounded-md bg-gray-50 p-3 text-sm text-gray-800">{{ $apa }}</p>
        <h3 class="mt-3 text-sm font-semibold text-gray-700">BibTeX</h3>
        <pre class="mt-1 overflow-x-auto rounded-md bg-gray-50 p-3 text-xs text-gray-800">{{ $bibtex }}</pre>
    </article>
</x-public-layout>
