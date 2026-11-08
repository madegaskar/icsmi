<x-app-layout>
    <x-slot name="header">
        <x-workflow-header
            back-href="{{ route('author.dashboard') }}"
            back-label="Back to My Manuscripts"
            :code="$manuscript->code"
            :title="$manuscript->title"
            :status-label="$manuscript->status->label()" />
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        @can('revise', $manuscript)
            <x-card title="Submit Revision">
                <form method="POST" action="{{ route('author.manuscripts.revise', $manuscript) }}" enctype="multipart/form-data">
                    @csrf
                    @include('manuscripts.upload-fields')

                    <div class="mt-4">
                        <x-input-label for="note" value="Note to the editor (optional)" />
                        <textarea id="note" name="note" rows="4" maxlength="5000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('note') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('note')" />
                    </div>

                    <div class="mt-4"><x-primary-button>Submit Revision</x-primary-button></div>
                </form>
            </x-card>
        @endcan

        @if ($reviews->isNotEmpty())
            <x-card title="Reviewer Comments">
                <ul class="mt-3 space-y-4 text-sm">
                    @foreach ($reviews as $review)
                        <li>
                            <p class="font-medium text-gray-700">Round {{ $review->round }}, Review {{ $loop->iteration }}</p>
                            <p class="mt-1 whitespace-pre-line text-gray-800">{{ $review->comments_for_author }}</p>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        <x-card title="Details">
            @include('manuscripts.summary')

            <h4 class="mt-5 text-sm font-medium text-gray-700">Authors</h4>
            <ul class="mt-1 space-y-1 text-sm text-gray-800">
                @foreach ($manuscript->authors as $author)
                    <li>{{ $author->name }}, {{ $author->affiliation }}@if ($author->is_corresponding) (corresponding)@endif</li>
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
                        <p class="font-medium text-gray-800">{{ $history->to_status->label() }} <span class="font-normal text-gray-500">· {{ $history->created_at->format('j M Y H:i') }}</span></p>
                        @if ($history->note)
                            <p class="mt-1 whitespace-pre-line text-gray-700">{{ $history->note }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-card>
    </div>
</x-app-layout>
