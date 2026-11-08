<x-public-layout :title="$issue->title" :description="strip_tags(\App\Support\SafeMarkdown::render($issue->description))">
    <x-slot:head>
        {{ \App\Support\JsonLd::script([
            '@type' => 'PublicationIssue',
            'name' => $issue->title,
            'datePublished' => $issue->year ? (string) $issue->year : null,
            'url' => route('proceedings.show', $issue->slug),
        ]) }}
    </x-slot:head>
    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold">{{ $issue->title }}</h1>

        <dl class="mt-6 max-w-xl divide-y divide-gray-200 border-y border-gray-200 text-sm">
            <div class="grid grid-cols-3 gap-4 py-2.5"><dt class="font-semibold text-gray-900">Year</dt><dd class="col-span-2 text-gray-700">{{ $issue->year }}</dd></div>
            @if ($issue->volume)<div class="grid grid-cols-3 gap-4 py-2.5"><dt class="font-semibold text-gray-900">Volume</dt><dd class="col-span-2 text-gray-700">{{ $issue->volume }}{{ $issue->number ? ', No. '.$issue->number : '' }}</dd></div>@endif
            @if ($issue->conference_dates)<div class="grid grid-cols-3 gap-4 py-2.5"><dt class="font-semibold text-gray-900">Dates</dt><dd class="col-span-2 text-gray-700">{{ $issue->conference_dates }}</dd></div>@endif
            @if ($issue->location)<div class="grid grid-cols-3 gap-4 py-2.5"><dt class="font-semibold text-gray-900">Location</dt><dd class="col-span-2 text-gray-700">{{ $issue->location }}</dd></div>@endif
            @if ($issue->isbn)<div class="grid grid-cols-3 gap-4 py-2.5"><dt class="font-semibold text-gray-900">ISBN</dt><dd class="col-span-2 text-gray-700">{{ $issue->isbn }}</dd></div>@endif
            @if ($issue->issn)<div class="grid grid-cols-3 gap-4 py-2.5"><dt class="font-semibold text-gray-900">ISSN</dt><dd class="col-span-2 text-gray-700">{{ $issue->issn }}</dd></div>@endif
        </dl>

        @if ($issue->description)
            <div class="content mt-6 max-w-2xl leading-relaxed text-gray-700">{{ \App\Support\SafeMarkdown::render($issue->description) }}</div>
        @endif

        <h2 class="mt-10 text-xl font-semibold">Articles</h2>

        <ul class="mt-2 divide-y divide-gray-200">
            @forelse ($articles as $article)
                @include('site.partials.article-row')
            @empty
                <li class="py-4 text-gray-600">No articles have been published in these proceedings yet.</li>
            @endforelse
        </ul>

        <div class="mt-6">{{ $articles->links() }}</div>
    </div>
</x-public-layout>
