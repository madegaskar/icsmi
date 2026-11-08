<x-app-layout>
    <x-slot name="header">
        <x-workflow-header
            back-href="{{ route('editor.dashboard') }}"
            back-label="Back to Manuscripts"
            :code="$manuscript->code"
            :title="$manuscript->title"
            :status-label="$manuscript->status->label()"
            :meta="'Version '.$manuscript->current_version" />
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        @if ($transitions !== [])
            <x-card title="Change Status">
                <form method="POST" action="{{ route('editor.manuscripts.status', $manuscript) }}">
                    @csrf

                    <div class="mt-3">
                        <x-input-label for="status" value="New status" />
                        <select id="status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($transitions as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="note" value="Note to the author (optional)" />
                        <textarea id="note" name="note" rows="4" maxlength="5000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('note') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('note')" />
                    </div>

                    <div class="mt-4"><x-primary-button>Update Status</x-primary-button></div>
                </form>
            </x-card>
        @endif

        @if ($manuscript->status === \App\Enums\ManuscriptStatus::Accepted)
            <x-card title="Publish Article">
                @if ($issues->isEmpty())
                    <p class="mt-3 text-sm text-gray-600">Create proceedings first: <a class="text-emerald-800 underline" href="{{ route('editor.issues.create') }}">New Proceedings</a></p>
                @else
                    <form method="POST" action="{{ route('editor.manuscripts.publish', $manuscript) }}" enctype="multipart/form-data" class="mt-3 grid gap-4 sm:grid-cols-2">
                        @csrf

                        <div class="sm:col-span-2">
                            <x-input-label for="issue_id" value="Proceedings" />
                            <select id="issue_id" name="issue_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($issues as $issue)
                                    <option value="{{ $issue->id }}">{{ $issue->title }}{{ $issue->published_at ? '' : ' (not published yet)' }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('issue_id')" />
                        </div>

                        <x-field name="doi" label="DOI (optional)" class="sm:col-span-2" />
                        <x-field name="first_page" label="First page" type="number" />
                        <x-field name="last_page" label="Last page" type="number" />

                        <div class="sm:col-span-2">
                            <x-input-label for="pdf" value="Final PDF (max 20 MB)" />
                            <input id="pdf" name="pdf" type="file" accept=".pdf" required class="mt-1 block w-full text-sm text-gray-700">
                            <x-input-error class="mt-2" :messages="$errors->get('pdf')" />
                            <x-input-error class="mt-2" :messages="$errors->get('publish')" />
                        </div>

                        <div class="sm:col-span-2"><x-primary-button>Publish</x-primary-button></div>
                    </form>
                @endif
            </x-card>
        @endif

        @if ($manuscript->article)
            <x-card title="Published Article">
                <p class="mt-3 text-sm"><a class="text-emerald-800 underline" href="{{ route('editor.articles.edit', $manuscript->article) }}">Edit article details</a></p>
            </x-card>
        @endif

        <x-card title="Reviews">
            <x-input-error class="mt-2" :messages="$errors->get('assignment')" />

            <ul class="mt-3 divide-y divide-gray-100 text-sm">
                @forelse ($manuscript->assignments as $assignment)
                    <li class="py-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p><span class="font-medium">{{ $assignment->reviewer->name }}</span> <span class="text-gray-500">· Round {{ $assignment->round }} · Due {{ $assignment->due_at->format('j M Y') }}</span></p>
                            <x-status-badge :label="$assignment->status->label()" />
                        </div>

                        @if ($assignment->completed_at)
                            <p class="mt-2"><span class="font-medium">Recommendation:</span> {{ $assignment->recommendation->label() }}</p>
                            <p class="mt-2 font-medium">Comments for the author</p>
                            <p class="whitespace-pre-line text-gray-700">{{ $assignment->comments_for_author }}</p>
                            @if ($assignment->comments_for_editor)
                                <p class="mt-2 font-medium">Confidential comments</p>
                                <p class="whitespace-pre-line text-gray-700">{{ $assignment->comments_for_editor }}</p>
                            @endif
                        @elseif (in_array($assignment->status, [\App\Enums\ReviewStatus::Invited, \App\Enums\ReviewStatus::Accepted], true))
                            <form method="POST" action="{{ route('editor.assignments.cancel', $assignment) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="text-xs text-gray-600 underline hover:text-gray-900">Cancel assignment</button>
                            </form>
                        @endif
                    </li>
                @empty
                    <li class="py-2 text-gray-600">No reviewers assigned yet.</li>
                @endforelse
            </ul>

            @if ($manuscript->status === \App\Enums\ManuscriptStatus::InReview)
                <form method="POST" action="{{ route('editor.manuscripts.reviewers', $manuscript) }}" class="mt-4 grid gap-3 border-t border-gray-100 pt-4 sm:grid-cols-3 sm:items-end">
                    @csrf

                    <div>
                        <x-input-label for="reviewer_id" value="Reviewer" />
                        <select id="reviewer_id" name="reviewer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select</option>
                            @foreach ($candidates as $candidate)
                                <option value="{{ $candidate->id }}">{{ $candidate->name }}{{ $candidate->affiliation ? ', '.$candidate->affiliation : '' }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('reviewer_id')" />
                    </div>

                    <div>
                        <x-input-label for="due_at" value="Due date" />
                        <x-text-input id="due_at" name="due_at" type="date" class="mt-1 block w-full" required />
                        <x-input-error class="mt-2" :messages="$errors->get('due_at')" />
                    </div>

                    <div><x-primary-button>Invite Reviewer</x-primary-button></div>
                </form>
            @endif
        </x-card>

        <x-card title="Details">
            @include('manuscripts.summary')

            @if ($manuscript->publicSubmission)
                <h4 class="mt-5 text-sm font-medium text-gray-700">From the public form</h4>
                <p class="mt-1 text-sm text-gray-800"><a class="text-emerald-800 hover:underline" href="{{ route('editor.submissions.show', $manuscript->publicSubmission) }}">{{ $manuscript->publicSubmission->code }}</a> · Phone: {{ $manuscript->publicSubmission->phone }}</p>
            @endif

            <h4 class="mt-5 text-sm font-medium text-gray-700">Authors</h4>
            <ul class="mt-1 space-y-1 text-sm text-gray-800">
                @foreach ($manuscript->authors as $author)
                    <li>{{ $author->name }}, {{ $author->affiliation }} ({{ $author->email }})@if ($author->is_corresponding) · corresponding @endif</li>
                @endforeach
            </ul>
        </x-card>

        <x-card title="Files">
            @include('manuscripts.files', ['files' => $manuscript->files])
        </x-card>

        <x-card title="History">
            <ul class="mt-3 space-y-3 text-sm">
                @foreach ($manuscript->histories as $history)
                    <li>
                        <p class="font-medium text-gray-800">{{ $history->to_status->label() }} <span class="font-normal text-gray-500">· {{ $history->actor->name }} · {{ $history->created_at->format('j M Y H:i') }}</span></p>
                        @if ($history->note)
                            <p class="mt-1 whitespace-pre-line text-gray-700">{{ $history->note }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-card>
    </div>
</x-app-layout>
