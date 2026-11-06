<section data-block="proceedings" class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
    <h2 class="text-3xl font-bold">{{ $block->heading }}</h2>

    @if ($latest)
        <div class="mt-4 rounded-lg border border-gray-200 p-5">
            <p class="text-base text-gray-500">
                @if ($latest->volume) Vol. {{ $latest->volume }}@if ($latest->number), No. {{ $latest->number }}@endif @endif
                ({{ $latest->year }})
            </p>
            <p class="mt-1 text-xl font-semibold">
                <a class="text-emerald-900 hover:underline" href="{{ route('proceedings.show', $latest->slug) }}">{{ $latest->title }}</a>
            </p>
            @if ($latest->published_at)
                <p class="mt-1 text-base text-gray-600">Published: {{ $latest->published_at->format('Y-m-d') }}</p>
            @endif

            <ul class="mt-4 divide-y divide-gray-200">
                @foreach ($articles as $article)
                    @include('site.partials.article-row')
                @endforeach
            </ul>
        </div>

        <p class="mt-4 text-base"><a class="text-emerald-800 hover:underline" href="{{ route('proceedings.archives') }}">View All Issues</a></p>
    @else
        <p class="mt-3 text-gray-600">No proceedings have been published yet.</p>
    @endif
</section>
