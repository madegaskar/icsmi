<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Board and Speakers</h2>
            <x-link-button :href="route('admin.board.create')">Add Person</x-link-button>
        </div>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-data-table :headers="['Name', 'List', 'Position', '']">
            @forelse ($members as $member)
                <tr>
                    <td class="px-4 py-3"><a class="font-medium hover:text-emerald-800" href="{{ route('admin.board.edit', $member) }}">{{ $member->name }}</a></td>
                    <td class="px-4 py-3 text-gray-600">{{ ucfirst($member->group) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $member->position }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.board.destroy', $member) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-gray-600 underline hover:text-gray-900">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-gray-600">No people yet.</td></tr>
            @endforelse
        </x-data-table>

        <div>{{ $members->links() }}</div>
    </div>
</x-app-layout>
