<section data-block="announcements" class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
    <h2 class="text-3xl font-bold">{{ $block->heading }}</h2>

    <ul class="mt-3 space-y-4">
        @forelse ($announcements as $announcement)
            <li>
                <p class="font-medium">{{ $announcement->title }}</p>
                <p class="text-xs text-gray-500">{{ $announcement->published_at->format('j F Y') }}</p>
            </li>
        @empty
            <li class="text-gray-600">No announcements yet.</li>
        @endforelse
    </ul>

    <p class="mt-4 text-base"><a class="text-emerald-800 hover:underline" href="{{ route('announcements') }}">All announcements</a></p>
</section>
