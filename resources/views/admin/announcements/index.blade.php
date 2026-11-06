<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Announcements</h2>
            <x-link-button :href="route('admin.announcements.create')">New Announcement</x-link-button>
        </div>
        @include('admin.pages.tabs')
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-data-table :headers="['Title', 'Category', 'Status', '']">
            @forelse ($announcements as $announcement)
                <tr>
                    <td class="px-4 py-3"><a class="font-medium hover:text-emerald-800" href="{{ route('admin.announcements.edit', $announcement) }}">{{ $announcement->title }}</a></td>
                    <td class="px-4 py-3 text-gray-600">{{ $announcement->categoryLabel() }}</td>
                    <td class="px-4 py-3">
                        <x-status-badge :label="$announcement->published_at ? ($announcement->ends_at && $announcement->ends_at->isPast() ? 'Ended' : 'Published') : 'Draft'" />
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-gray-600 underline hover:text-gray-900">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-gray-600">No announcements yet.</td></tr>
            @endforelse
        </x-data-table>

        <div>{{ $announcements->links() }}</div>
    </div>
</x-app-layout>
