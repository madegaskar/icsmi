<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Articles</h2>
            <x-link-button :href="route('editor.articles.create')">Add Existing Article</x-link-button>
        </div>
        @include('editor.partials.tabs')
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-data-table :headers="['Title', 'Proceedings', 'Status']">
            @forelse ($articles as $article)
                <tr>
                    <td class="px-4 py-3"><a class="font-medium hover:text-emerald-800" href="{{ route('editor.articles.edit', $article) }}">{{ $article->title }}</a></td>
                    <td class="px-4 py-3 text-gray-600">{{ $article->issue->title }}</td>
                    <td class="px-4 py-3"><x-status-badge :label="$article->published_at ? 'Published' : 'Draft'" /></td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-6 text-gray-600">No articles yet.</td></tr>
            @endforelse
        </x-data-table>

        <div>{{ $articles->links() }}</div>
    </div>
</x-app-layout>
