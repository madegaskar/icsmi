@props(['label'])

@php
    $isRejected = $label === 'Rejected';
@endphp

<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $isRejected ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-900' }}">
    @if ($isRejected)
        <svg aria-hidden="true" focusable="false" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 shrink-0">
            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
        </svg>
    @endif
    {{ $label }}
</span>
