<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pages</h2>
            <x-link-button :href="route('admin.pages.create')">New Page</x-link-button>
        </div>
        @include('admin.pages.tabs')
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-data-table :headers="['Title', 'Address', 'Status', 'Updated', '']">
            @forelse ($pages as $page)
                <tr>
                    <td class="px-4 py-3"><a class="font-medium hover:text-emerald-800" href="{{ route('admin.pages.edit', $page) }}">{{ $page->title }}</a></td>
                    <td class="px-4 py-3 text-gray-600">/{{ $page->slug }}</td>
                    <td class="px-4 py-3"><x-status-badge :label="ucfirst($page->state())" /></td>
                    <td class="px-4 py-3 text-gray-600">{{ $page->updated_at->format('j M Y') }}</td>
                    <td class="space-x-3 px-4 py-3 text-right text-xs">
                        <a class="text-gray-600 underline hover:text-gray-900" href="{{ route('admin.pages.preview', $page) }}">Preview</a>
                        <a class="text-gray-600 underline hover:text-gray-900" href="{{ route('admin.pages.revisions', $page) }}">History</a>
                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-600 underline hover:text-gray-900">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-gray-600">No pages yet.</td></tr>
            @endforelse
        </x-data-table>
    </div>
</x-app-layout>
