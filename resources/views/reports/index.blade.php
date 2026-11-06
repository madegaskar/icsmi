@php
    $titles = [
        'monthly' => 'Manuscripts per Month',
        'statuses' => 'Manuscripts per Status',
        'issues' => 'Proceedings',
        'articles' => 'Articles: Views and Downloads',
        'reviewers' => 'Reviewer Workload',
        'manuscripts' => 'Manuscript List',
    ];
    $tiles = [
        ['Manuscripts', $summary['total'], null],
        ['Accepted', $summary['accepted'], $summary['acceptance_rate'] !== null ? $summary['acceptance_rate'].'% of decided' : null],
        ['Rejected', $summary['rejected'], $summary['rejection_rate'] !== null ? $summary['rejection_rate'].'% of decided' : null],
        ['Days to decision', $summary['days_to_decision'] ?? 'n/a', 'average'],
        ['Days to publication', $summary['days_to_publication'] ?? 'n/a', 'average'],
        ['Views', $summary['views'], null],
        ['Downloads', $summary['downloads'], null],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3 px-4 sm:px-0">
            <div>
                <x-input-label for="from" value="From" />
                <x-text-input id="from" name="from" type="date" class="mt-1" :value="$filters['from'] ?? ''" />
                <x-input-error class="mt-2" :messages="$errors->get('from')" />
            </div>
            <div>
                <x-input-label for="to" value="To" />
                <x-text-input id="to" name="to" type="date" class="mt-1" :value="$filters['to'] ?? ''" />
                <x-input-error class="mt-2" :messages="$errors->get('to')" />
            </div>
            <x-primary-button>Apply</x-primary-button>
        </form>

        <dl class="grid grid-cols-2 gap-px overflow-hidden bg-gray-200 shadow-sm sm:rounded-lg md:grid-cols-4 lg:grid-cols-7">
            @foreach ($tiles as [$label, $value, $hint])
                <div class="bg-white p-4 last:col-span-2 md:last:col-span-2 lg:last:col-span-1">
                    <dt class="text-xs text-gray-500">{{ $label }}</dt>
                    <dd class="mt-1 text-2xl font-semibold tabular-nums text-gray-900">{{ $value }}</dd>
                    @if ($hint)
                        <dd class="mt-0.5 text-xs text-gray-500">{{ $hint }}</dd>
                    @endif
                </div>
            @endforeach
        </dl>

        @foreach ($titles as $key => $title)
            <section>
                <div class="flex flex-wrap items-center justify-between gap-2 px-4 sm:px-0">
                    <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
                    <p class="flex gap-3 text-sm">
                        <a class="text-emerald-800 hover:underline" href="{{ route('reports.export', ['section' => $key, 'format' => 'csv'] + $filters) }}">CSV</a>
                        <a class="text-emerald-800 hover:underline" href="{{ route('reports.export', ['section' => $key, 'format' => 'xlsx'] + $filters) }}">Excel</a>
                    </p>
                </div>

                <div class="mt-2">
                    <x-data-table :headers="$sections[$key]['headers']">
                        @forelse ($sections[$key]['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td class="px-4 py-3 text-gray-700">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($sections[$key]['headers']) }}" class="px-4 py-6 text-gray-600">No data for this period.</td></tr>
                        @endforelse
                    </x-data-table>
                </div>
            </section>
        @endforeach
    </div>
</x-app-layout>
