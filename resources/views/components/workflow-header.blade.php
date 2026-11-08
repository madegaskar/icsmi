@props(['backHref', 'backLabel', 'code', 'title', 'statusLabel', 'meta' => null, 'authors' => null])

<div data-workflow-header class="space-y-2">
    <nav aria-label="Workflow navigation" class="text-sm">
        <a href="{{ $backHref }}" class="text-emerald-800 hover:underline">{{ $backLabel }}</a>
    </nav>
    <p class="text-sm text-gray-500">
        <span>{{ $code }}</span>
        @if ($authors)
            <span aria-hidden="true" class="mx-1 text-gray-400">/</span>
            <span>{{ $authors }}</span>
        @endif
        <span aria-hidden="true" class="mx-1 text-gray-400">/</span>
        <span>{{ $title }}</span>
    </p>
    <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
        <x-status-badge :label="$statusLabel" />
        @if ($meta)
            <span class="text-gray-600">{{ $meta }}</span>
        @endif
    </p>
</div>
