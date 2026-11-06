<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">History: {{ $page->title }}</h2>
        @include('admin.pages.tabs')
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <p class="text-sm text-gray-600">Every save is kept here and cannot be changed or removed. Restoring an older version copies its title and content back, and the restore is recorded as a new entry.</p>

        <x-data-table :headers="['Saved', 'By', 'Title', 'Address', '']">
            @forelse ($revisions as $revision)
                <tr>
                    <td class="px-4 py-3 text-gray-600">{{ $revision->created_at->format('j M Y H:i') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $revision->author?->name ?? 'Deleted user' }}</td>
                    <td class="px-4 py-3">{{ $revision->title }}</td>
                    <td class="px-4 py-3 text-gray-600">/{{ $revision->slug }}</td>
                    <td class="px-4 py-3 text-right text-xs">
                        <form method="POST" action="{{ route('admin.pages.revisions.restore', [$page, $revision]) }}">
                            @csrf
                            <button type="submit" class="text-gray-600 underline hover:text-gray-900">Restore title and content</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-gray-600">No saved versions yet.</td></tr>
            @endforelse
        </x-data-table>

        <div>{{ $revisions->links() }}</div>
    </div>
</x-app-layout>
