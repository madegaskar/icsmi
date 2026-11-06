<x-app-layout>
    <x-slot name="header">
        <p class="text-sm text-gray-500">Public Submission</p>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $submission->code }}</h2>
        <p class="mt-2"><x-status-badge :label="$submission->confirmed_at ? 'Confirmed' : 'Awaiting confirmation'" /></p>
        @include('editor.partials.tabs')
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-card title="Details">
            <dl class="mt-3 space-y-3 text-sm">
                <div><dt class="font-medium text-gray-700">Title</dt><dd class="mt-1 text-gray-800">{{ $submission->title }}</dd></div>
                <div><dt class="font-medium text-gray-700">Name</dt><dd class="mt-1 text-gray-800">{{ $submission->name }}</dd></div>
                <div><dt class="font-medium text-gray-700">Email</dt><dd class="mt-1 break-all text-gray-800">{{ $submission->email }}</dd></div>
                <div><dt class="font-medium text-gray-700">Phone</dt><dd class="mt-1 text-gray-800">{{ $submission->phone }}</dd></div>
                <div><dt class="font-medium text-gray-700">Institution Address</dt><dd class="mt-1 whitespace-pre-line text-gray-800">{{ $submission->institution_address }}</dd></div>
                <div><dt class="font-medium text-gray-700">Abstract</dt><dd class="mt-1 whitespace-pre-line text-gray-800">{{ $submission->abstract }}</dd></div>
                <div><dt class="font-medium text-gray-700">Keyword</dt><dd class="mt-1 text-gray-800">{{ $submission->keywords }}</dd></div>
                <div><dt class="font-medium text-gray-700">Received</dt><dd class="mt-1 text-gray-800">{{ $submission->created_at->format('j F Y H:i') }}</dd></div>
            </dl>

            <p class="mt-5 text-sm">
                <a class="font-medium text-emerald-800 hover:underline" href="{{ route('editor.submissions.file', $submission) }}">Download file</a>
                <span class="break-all text-gray-500">({{ $submission->original_name }}, {{ number_format($submission->size / 1024, 0) }} KB)</span>
            </p>
        </x-card>

        @if ($submission->manuscript)
            <x-card title="Manuscript">
                @can('manage', $submission->manuscript)
                    <p class="mt-3 text-sm"><a class="font-medium text-emerald-800 hover:underline" href="{{ route('editor.manuscripts.show', $submission->manuscript) }}">{{ $submission->manuscript->code }}</a></p>
                @else
                    <p class="mt-3 text-sm text-gray-700">{{ $submission->manuscript->code }}</p>
                @endcan
            </x-card>
        @endif
    </div>
</x-app-layout>
