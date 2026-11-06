<section data-block="text" class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
    @if ($block->heading)
        <h2 class="text-3xl font-bold">{{ $block->heading }}</h2>
    @endif

    <div class="content mt-3">{{ $block->bodyHtml() }}</div>
</section>
