<x-public-layout title="Announcements">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold">Announcements</h1>

        <nav class="mt-4 flex flex-wrap gap-4 text-sm">
            <a class="{{ $category === null ? 'font-semibold text-emerald-900' : 'text-gray-600 hover:text-gray-900' }}" href="{{ route('announcements') }}">All</a>
            @foreach (\App\Models\Announcement::CATEGORIES as $value)
                <a class="{{ $category === $value ? 'font-semibold text-emerald-900' : 'text-gray-600 hover:text-gray-900' }}" href="{{ route('announcements', ['category' => $value]) }}">{{ \App\Models\Announcement::labelFor($value) }}</a>
            @endforeach
        </nav>

        <ul class="mt-6 divide-y divide-gray-200">
            @forelse ($announcements as $announcement)
                <li class="py-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">{{ $announcement->categoryLabel() }}</p>
                    <h2 class="mt-1 font-semibold">{{ $announcement->title }}</h2>
                    <p class="text-xs text-gray-500">{{ $announcement->published_at->format('j F Y') }}</p>
                    <p class="mt-2 whitespace-pre-line text-gray-700">{{ $announcement->body }}</p>
                </li>
            @empty
                <li class="py-4 text-gray-600">No announcements yet.</li>
            @endforelse
        </ul>

        <div class="mt-6">{{ $announcements->links() }}</div>
    </div>
</x-public-layout>
