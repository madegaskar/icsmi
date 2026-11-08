<div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
            <tr>
                <th class="px-4 py-3">Code</th>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Submitted</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($manuscripts as $manuscript)
                <tr>
                    <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $manuscript->code }}</td>
                    <td class="px-4 py-3"><a class="font-medium text-gray-900 hover:text-emerald-800" href="{{ route($showRoute, $manuscript) }}">{{ $manuscript->title }}</a></td>
                    <td class="whitespace-nowrap px-4 py-3"><x-status-badge :label="$manuscript->status->label()" /></td>
                    <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $manuscript->submitted_at->format('j M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-gray-600">No manuscripts yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $manuscripts->links() }}</div>
