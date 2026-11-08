<x-public-layout>
    @foreach ($blocks as $block)
        @include('site.blocks.'.$block->type, ['block' => $block])
    @endforeach

    @if ($blocks->isEmpty())
        <p class="px-4 py-12 text-gray-600 sm:px-6">Nothing is shown on the home page yet.</p>
    @endif
</x-public-layout>
