<x-public-layout title="Proceedings Archives">
    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold">Proceedings Archives</h1>

        <ul class="mt-6 divide-y divide-gray-200">
            @forelse ($issues as $issue)
                <li class="py-4">
                    <div class="{{ $issue->coverMedia ? 'grid items-start gap-6 sm:grid-cols-[10rem_minmax(0,1fr)]' : '' }}">
                        @if ($issue->coverMedia)
                            <figure data-cover-image class="relative aspect-[3/4] overflow-hidden rounded-md border border-gray-200 shadow-sm">
                                <img src="{{ route('media.show', [$issue->coverMedia, 'desktop']) }}" alt="{{ $issue->coverMedia->alt_text }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                            </figure>
                        @endif

                        <div>
                            <a href="{{ route('proceedings.show', $issue->slug) }}" class="text-lg font-medium hover:text-emerald-800">{{ $issue->title }}</a>
                            <p class="mt-1 text-sm text-gray-600">{{ $issue->year }} · {{ $issue->articles_count }} {{ Str::plural('article', $issue->articles_count) }}</p>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-4 text-gray-600">No proceedings have been published yet.</li>
            @endforelse
        </ul>

        <div class="mt-6">{{ $issues->links() }}</div>
    </div>
</x-public-layout>
