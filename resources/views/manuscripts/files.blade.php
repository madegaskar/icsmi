<ul class="mt-3 divide-y divide-gray-100 text-sm">
    @forelse ($files as $file)
        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
            <span class="break-all text-gray-700">v{{ $file->version }} · {{ ucfirst($file->kind) }} · {{ $file->original_name }} · {{ number_format($file->size / 1024, 0) }} KB</span>
            <a class="font-medium text-emerald-800 hover:underline" href="{{ route('manuscript-files.download', $file) }}">Download</a>
        </li>
    @empty
        <li class="py-2 text-gray-600">No files.</li>
    @endforelse
</ul>
