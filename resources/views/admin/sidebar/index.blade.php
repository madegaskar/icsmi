<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sidebar</h2>
            <x-link-button :href="route('admin.sidebar.create')">New Section</x-link-button>
        </div>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <p class="text-sm text-gray-600">The "Make a Submission" button stays at the top. Sections appear in the order of their Order number. Visitor statistics are filled in automatically.</p>

        <x-data-table :headers="['Order', 'Title', 'Type', 'Visible', '']">
            @forelse ($sections as $section)
                <tr>
                    <td class="px-4 py-3 text-gray-600">{{ $section->sort_order }}</td>
                    <td class="px-4 py-3 font-medium">{{ $section->title }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ ucfirst($section->kind) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $section->is_visible ? 'Yes' : 'No' }}</td>
                    <td class="space-x-3 px-4 py-3 text-right text-xs">
                        <a class="text-gray-600 underline hover:text-gray-900" href="{{ route('admin.sidebar.edit', $section) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.sidebar.destroy', $section) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-600 underline hover:text-gray-900">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-gray-600">No sections yet.</td></tr>
            @endforelse
        </x-data-table>
    </div>
</x-app-layout>
