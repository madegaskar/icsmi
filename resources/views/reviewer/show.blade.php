<x-app-layout>
    <x-slot name="header">
        <p class="text-sm text-gray-500">{{ $manuscript->code }} · Round {{ $assignment->round }}</p>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $manuscript->title }}</h2>
        <p class="mt-2"><x-status-badge :label="$assignment->status->label()" /> <span class="ms-2 text-sm text-gray-500">Due {{ $assignment->due_at->format('j F Y') }}</span></p>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-card title="Manuscript">
            @include('manuscripts.summary')

            @can('download', $file)
                <p class="mt-4"><a class="font-medium text-emerald-800 hover:underline" href="{{ route('manuscript-files.download', $file) }}">Download anonymized manuscript</a></p>
            @endcan
        </x-card>

        @can('respond', $assignment)
            <x-card title="Respond to Invitation">
                <form method="POST" action="{{ route('reviewer.assignments.respond', $assignment) }}" class="mt-3 flex gap-3">
                    @csrf
                    <button name="response" value="accept" class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">Accept</button>
                    <button name="response" value="decline" class="rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Decline</button>
                </form>
            </x-card>
        @endcan

        @can('submit', $assignment)
            <x-card title="Submit Review">
                <form method="POST" action="{{ route('reviewer.assignments.submit', $assignment) }}">
                    @csrf

                    <div class="mt-3">
                        <x-input-label for="recommendation" value="Recommendation" />
                        <select id="recommendation" name="recommendation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select</option>
                            @foreach (\App\Enums\ReviewRecommendation::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('recommendation') === $option->value)>{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('recommendation')" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="comments_for_author" value="Comments for the author (shared anonymously)" />
                        <textarea id="comments_for_author" name="comments_for_author" rows="8" maxlength="20000" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('comments_for_author') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('comments_for_author')" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="comments_for_editor" value="Confidential comments for the editor (optional)" />
                        <textarea id="comments_for_editor" name="comments_for_editor" rows="4" maxlength="20000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('comments_for_editor') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('comments_for_editor')" />
                    </div>

                    <div class="mt-4"><x-primary-button>Submit Review</x-primary-button></div>
                </form>
            </x-card>
        @endcan

        @if ($assignment->completed_at)
            <x-card title="Your Review">
                <dl class="mt-3 space-y-3 text-sm">
                    <div><dt class="font-medium text-gray-700">Recommendation</dt><dd class="mt-1">{{ $assignment->recommendation->label() }}</dd></div>
                    <div><dt class="font-medium text-gray-700">Comments for the author</dt><dd class="mt-1 whitespace-pre-line">{{ $assignment->comments_for_author }}</dd></div>
                </dl>
            </x-card>
        @endif
    </div>
</x-app-layout>
