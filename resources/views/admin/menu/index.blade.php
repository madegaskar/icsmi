<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Menu</h2>
            <x-link-button :href="route('admin.menu.create')">New Menu Item</x-link-button>
        </div>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-data-table :headers="['Menu', 'Label', 'Link', 'Visible', '']">
            @forelse ($items as $item)
                <tr>
                    <td class="px-4 py-3 text-gray-600">{{ $item->location === 'header' ? 'Header' : 'Footer' }}</td>
                    <td class="px-4 py-3 {{ $item->parent_id ? 'pl-10 text-gray-700' : 'font-medium' }}">{{ $item->label }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        @switch($item->link_type)
                            @case(\App\Models\MenuItem::TYPE_MEDIA) {{ $item->media?->original_name ?? 'File removed from library' }} @break
                            @case(\App\Models\MenuItem::TYPE_GROUP) Group heading @break
                            @default {{ $item->url }}
                        @endswitch
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->is_visible ? 'Yes' : 'No' }}</td>
                    <td class="space-x-3 px-4 py-3 text-right text-xs">
                        <a class="text-gray-600 underline hover:text-gray-900" href="{{ route('admin.menu.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.menu.destroy', $item) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-600 underline hover:text-gray-900">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-gray-600">No menu items yet.</td></tr>
            @endforelse
        </x-data-table>
    </div>
</x-app-layout>
