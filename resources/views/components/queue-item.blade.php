@props(['code', 'title', 'href', 'label', 'date'])

<li data-queue-item class="flex flex-wrap items-start gap-4 px-4 py-4 sm:flex-nowrap sm:items-center">
    <div class="min-w-0 flex-1">
        <p class="text-xs text-gray-500">{{ $code }}</p>
        <a href="{{ $href }}" class="mt-1 block font-medium text-gray-900 hover:text-emerald-800">{{ $title }}</a>
        <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-600">
            <x-status-badge :label="$label" />
            <span>{{ $date }}</span>
        </p>
    </div>
    <a href="{{ $href }}" class="shrink-0 rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">View<span class="sr-only"> {{ $title }}</span></a>
</li>
