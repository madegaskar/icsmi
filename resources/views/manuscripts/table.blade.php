<ul class="divide-y divide-gray-200 bg-white shadow-sm sm:rounded-lg" data-queue="manuscripts">
    @forelse ($manuscripts as $manuscript)
        <x-queue-item
            :code="$manuscript->code"
            :title="$manuscript->title"
            :href="route($showRoute, $manuscript)"
            :label="$manuscript->status->label()"
            :date="'Submitted '.$manuscript->submitted_at->format('j M Y')" />
    @empty
        <li class="px-4 py-6 text-gray-600">No manuscripts yet.</li>
    @endforelse
</ul>

<div class="mt-4">{{ $manuscripts->links() }}</div>
