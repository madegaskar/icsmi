<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Home Page</h2>
            <x-link-button :href="route('admin.home-blocks.create')">New Block</x-link-button>
        </div>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <p class="text-sm text-gray-600">Blocks appear in order of the Order number. A block that is hidden, or outside its start and end times, is not shown.</p>

        <x-data-table :headers="['Order', 'Block', 'Heading', 'Schedule', 'Visible', '']">
            @forelse ($blocks as $block)
                <tr>
                    <td class="px-4 py-3 text-gray-600">{{ $block->sort_order }}</td>
                    <td class="px-4 py-3 font-medium">{{ ucfirst($block->type) }}</td>
                    <td class="px-4 py-3">{{ $block->heading ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        {{ $block->starts_at ? 'From '.$block->starts_at->format('j M Y H:i') : 'Always' }}
                        @if ($block->ends_at) <br>Until {{ $block->ends_at->format('j M Y H:i') }} @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $block->is_visible ? 'Yes' : 'No' }}</td>
                    <td class="space-x-3 px-4 py-3 text-right text-xs">
                        <a class="text-gray-600 underline hover:text-gray-900" href="{{ route('admin.home-blocks.edit', $block) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.home-blocks.destroy', $block) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-600 underline hover:text-gray-900">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-gray-600">No blocks yet. The home page is empty until you add one.</td></tr>
            @endforelse
        </x-data-table>
    </div>
</x-app-layout>
