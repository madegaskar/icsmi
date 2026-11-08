<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Media Library</h2>
            <x-link-button :href="route('library.create')">Upload File</x-link-button>
        </div>
        @include('editor.partials.tabs')
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-data-table :headers="['Preview', 'File', 'Type', 'Alt text', 'Uploaded by', '']">
            @forelse ($media as $item)
                <tr>
                    <td class="px-4 py-3">
                        @if ($item->isImage())
                            <img src="{{ route('media.show', [$item, 'mobile']) }}" alt="{{ $item->alt_text }}" loading="lazy" class="h-14 w-20 rounded object-cover">
                        @else
                            <span class="inline-flex h-14 w-20 items-center justify-center rounded bg-gray-100 text-xs font-medium text-gray-600">PDF</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a class="font-medium hover:text-emerald-800" href="{{ route('media.show', [$item, 'original']) }}" target="_blank" rel="noopener">{{ $item->original_name }}</a>
                        @if ($item->caption)
                            <p class="text-xs text-gray-600">{{ $item->caption }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->isImage() ? $item->width.' × '.$item->height.' px' : 'PDF' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->alt_text ?? '(none)' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->uploader?->name ?? 'Deleted user' }}</td>
                    <td class="space-x-3 px-4 py-3 text-right text-xs">
                        <a class="text-gray-600 underline hover:text-gray-900" href="{{ route('library.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('library.destroy', $item) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-600 underline hover:text-gray-900">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-gray-600">No files yet.</td></tr>
            @endforelse
        </x-data-table>

        <div>{{ $media->links() }}</div>
    </div>
</x-app-layout>
