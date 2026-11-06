<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Review Assignments</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $assignment->manuscript->code }}</td>
                            <td class="px-4 py-3"><a class="font-medium text-gray-900 hover:text-emerald-800" href="{{ route('reviewer.assignments.show', $assignment) }}">{{ $assignment->manuscript->title }}</a></td>
                            <td class="whitespace-nowrap px-4 py-3"><x-status-badge :label="$assignment->status->label()" /></td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $assignment->due_at->format('j M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-gray-600">No assignments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $assignments->links() }}</div>
    </div>
</x-app-layout>
