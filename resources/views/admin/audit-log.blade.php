<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit Log</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('admin.audit-log') }}" class="flex flex-wrap items-end gap-3 px-4 sm:px-0">
            <div>
                <x-input-label for="action" value="Action" />
                <select id="action" name="action" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All</option>
                    @foreach ($actions as $option)
                        <option value="{{ $option }}" @selected($action === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <x-primary-button>Filter</x-primary-button>
        </form>

        <x-data-table :headers="['Time', 'User', 'Action', 'Details', 'IP']">
            @forelse ($logs as $log)
                <tr>
                    <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $log->created_at->format('j M Y H:i') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $log->user?->email }}</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ $log->action }}</td>
                    <td class="break-all px-4 py-3 font-mono text-xs text-gray-600">{{ $log->meta ? json_encode($log->meta) : '' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-gray-600">No entries.</td></tr>
            @endforelse
        </x-data-table>

        <div>{{ $logs->links() }}</div>
    </div>
</x-app-layout>
