<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Proceedings</h2>
            <x-link-button :href="route('editor.issues.create')">New Proceedings</x-link-button>
        </div>
        @include('editor.partials.tabs')
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-data-table :headers="['Title', 'Year', 'Articles', 'Status']">
            @forelse ($issues as $issue)
                <tr>
                    <td class="px-4 py-3"><a class="font-medium hover:text-emerald-800" href="{{ route('editor.issues.edit', $issue) }}">{{ $issue->title }}</a></td>
                    <td class="px-4 py-3 text-gray-600">{{ $issue->year }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $issue->articles_count }}</td>
                    <td class="px-4 py-3"><x-status-badge :label="$issue->published_at ? 'Published' : 'Draft'" /></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-gray-600">No proceedings yet.</td></tr>
            @endforelse
        </x-data-table>

        <div>{{ $issues->links() }}</div>
    </div>
</x-app-layout>
