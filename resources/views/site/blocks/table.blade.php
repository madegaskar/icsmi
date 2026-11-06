<section data-block="table" class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
    @if ($block->heading)
        <h2 class="text-left text-3xl font-bold">{{ $block->heading }}</h2>
    @endif

    <div class="mt-4 {{ $block->coverMedia ? 'grid items-stretch gap-8 md:grid-cols-[14rem_minmax(0,1fr)]' : '' }}">
        @if ($block->coverMedia)
            <figure data-cover-image class="relative aspect-[3/4] overflow-hidden rounded-md border border-gray-200 shadow-sm md:aspect-auto">
                <img src="{{ route('media.show', [$block->coverMedia, 'desktop']) }}" alt="{{ $block->coverMedia->alt_text }}" class="absolute inset-0 h-full w-full object-cover">
            </figure>
        @endif

        <table class="w-full border-collapse text-left text-base">
            <tbody>
                @foreach (preg_split('/\R/', (string) $block->body) as $line)
                    @continue(trim($line) === '')
                    @php([$label, $value] = array_pad(array_map('trim', explode('|', $line, 2)), 2, ''))
                    <tr class="border-b border-gray-200 align-top">
                        <th scope="row" class="w-48 py-2 pe-4 text-left font-semibold text-gray-900">{{ $label }}</th>
                        <td class="py-2 text-left text-gray-700">{{ $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
