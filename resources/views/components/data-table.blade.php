@props(['headers'])

<div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
            <tr>
                @foreach ($headers as $header)
                    <th class="whitespace-nowrap px-4 py-3">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            {{ $slot }}
        </tbody>
    </table>
</div>
