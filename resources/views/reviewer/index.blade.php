<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Review Assignments</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <ul class="divide-y divide-gray-200 bg-white shadow-sm sm:rounded-lg" data-queue="assignments">
            @forelse ($assignments as $assignment)
                <x-queue-item
                    :code="$assignment->manuscript->code"
                    :title="$assignment->manuscript->title"
                    :href="route('reviewer.assignments.show', $assignment)"
                    :label="$assignment->status->label()"
                    :date="'Due '.$assignment->due_at->format('j M Y')" />
            @empty
                <li class="px-4 py-6 text-gray-600">No assignments yet.</li>
            @endforelse
        </ul>

        <div>{{ $assignments->links() }}</div>
    </div>
</x-app-layout>
