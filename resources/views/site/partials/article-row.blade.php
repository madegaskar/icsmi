<li class="py-4">
    <div class="flex items-end justify-between gap-4">
        <div class="min-w-0">
            <a href="{{ route('articles.show', $article->slug) }}" class="font-medium text-gray-900 hover:text-emerald-800">{{ $article->title }}</a>
            <p class="mt-1 text-sm text-gray-600">{{ $article->authors->pluck('name')->join(', ') }}</p>
            @if ($article->pages())
                <p class="mt-1 text-xs text-gray-500">pp. {{ $article->pages() }}</p>
            @endif
            @if (array_key_exists('views_total', $article->getAttributes()))
                <p class="mt-1 text-xs text-gray-500">Abstract views: {{ $article->views_total ?? 0 }} · PDF Downloads: {{ $article->downloads_total ?? 0 }}</p>
            @endif
            @if ($article->doi)
                <p class="mt-1 text-xs text-gray-500">DOI: <a href="https://doi.org/{{ rawurlencode($article->doi) }}" rel="noopener" class="text-emerald-800 hover:underline">https://doi.org/{{ $article->doi }}</a></p>
            @endif
        </div>

        @if ($article->pdf_path)
            <a href="{{ route('articles.pdf', $article->slug) }}" class="shrink-0 rounded-md bg-emerald-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-800">PDF</a>
        @endif
    </div>
</li>
