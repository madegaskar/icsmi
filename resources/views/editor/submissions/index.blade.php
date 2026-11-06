<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Public Submissions</h2>
        @include('editor.partials.tabs')
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <p class="px-4 text-sm text-gray-600 sm:px-0">Confirmed submissions continue in the Manuscripts list. Unconfirmed ones are deleted after {{ \App\Models\PublicSubmission::CONFIRMATION_DAYS }} days.</p>

        <x-data-table :headers="['Reference', 'Title', 'Name', 'Email', 'Received', 'Status']">
            @forelse ($submissions as $submission)
                <tr>
                    <td class="whitespace-nowrap px-4 py-3"><a class="font-medium hover:text-emerald-800" href="{{ route('editor.submissions.show', $submission) }}">{{ $submission->code }}</a></td>
                    <td class="px-4 py-3 text-gray-700">{{ $submission->title }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $submission->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $submission->email }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $submission->created_at->format('j M Y') }}</td>
                    <td class="whitespace-nowrap px-4 py-3"><x-status-badge :label="$submission->confirmed_at ? 'Confirmed' : 'Awaiting confirmation'" /></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-gray-600">No submissions yet.</td></tr>
            @endforelse
        </x-data-table>

        <div>{{ $submissions->links() }}</div>
    </div>
</x-app-layout>
