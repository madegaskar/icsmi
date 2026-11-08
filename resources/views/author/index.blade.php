<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Submissions</h2>
            <a href="{{ route('author.manuscripts.create') }}" class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">New Submission</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <nav aria-label="Workspace tabs" class="flex flex-wrap gap-6 border-b border-gray-200 px-4 text-sm sm:px-0">
            <a href="{{ route('author.dashboard', ['tab' => 'queue']) }}" @if ($tab === 'queue') aria-current="page" @endif class="-mb-px inline-flex items-center gap-2 border-b-2 pb-3 {{ $tab === 'queue' ? 'border-emerald-800 font-medium text-gray-900' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                My Queue
                <span class="rounded-full bg-gray-100 px-2 text-xs text-gray-700">{{ $queueCount }}</span>
            </a>
            <a href="{{ route('author.dashboard', ['tab' => 'archive']) }}" @if ($tab === 'archive') aria-current="page" @endif class="-mb-px inline-flex items-center gap-2 border-b-2 pb-3 {{ $tab === 'archive' ? 'border-emerald-800 font-medium text-gray-900' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                Archives
                <span class="rounded-full bg-gray-100 px-2 text-xs text-gray-700">{{ $archiveCount }}</span>
            </a>
        </nav>

        <form method="GET" action="{{ route('author.dashboard') }}" class="flex flex-wrap items-center gap-2 px-4 sm:px-0">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label for="q" class="sr-only">Search by title or code</label>
            <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search by title or code" class="w-full max-w-md rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-auto">
            <button type="submit" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Search</button>
        </form>

        <ul class="divide-y divide-gray-200 bg-white shadow-sm sm:rounded-lg">
            @forelse ($manuscripts as $manuscript)
                <li class="flex flex-wrap items-start gap-4 px-4 py-4 sm:flex-nowrap sm:items-center">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-500">{{ $manuscript->code }}</p>
                        <a href="{{ route('author.manuscripts.show', $manuscript) }}" class="mt-1 block font-medium text-gray-900 hover:text-emerald-800">{{ $manuscript->title }}</a>
                        <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-600">
                            <x-status-badge :label="$manuscript->status->label()" />
                            <span>Submitted {{ $manuscript->submitted_at->format('j M Y') }}</span>
                        </p>
                    </div>
                    <a href="{{ route('author.manuscripts.show', $manuscript) }}" class="shrink-0 rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">View<span class="sr-only"> {{ $manuscript->title }}</span></a>
                </li>
            @empty
                <li class="px-4 py-6 text-gray-600">
                    @if ($search !== '')
                        No manuscripts match your search.
                    @elseif ($tab === 'archive')
                        No archived manuscripts yet.
                    @else
                        No manuscripts in your queue.
                    @endif
                </li>
            @endforelse
        </ul>

        <div>{{ $manuscripts->links() }}</div>
    </div>
</x-app-layout>
