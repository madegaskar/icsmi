@php
    $image = $block->desktopMedia ?? $block->mobileMedia;
    $imageVariant = $block->desktopMedia ? 'desktop' : 'mobile';
@endphp

<section data-block="hero" class="relative overflow-hidden bg-emerald-950 text-white">
    <div class="mx-auto grid max-w-6xl items-stretch {{ $image ? 'lg:grid-cols-2' : '' }}">
        <div class="relative z-10 flex flex-col justify-center px-4 py-16 sm:px-6 sm:py-20 lg:py-28">
            @if ($block->subtitle)
                <p class="text-sm font-semibold tracking-widest text-emerald-200">{{ $block->subtitle }}</p>
            @endif

            <h1 class="mt-3 max-w-xl text-3xl font-bold leading-tight sm:text-5xl">{{ $block->heading }}</h1>

            @if ($block->button_label && $block->button_url)
                <div class="mt-8">
                    <a href="{{ $block->button_url }}" class="inline-block rounded-md bg-white px-4 py-2 text-sm font-medium text-emerald-950 hover:bg-emerald-100">{{ $block->button_label }}</a>
                </div>
            @endif
        </div>

        @if ($image)
            <div class="relative order-first h-64 sm:h-80 lg:order-last lg:h-auto lg:min-h-[28rem]">
                <picture class="absolute inset-0 block h-full w-full">
                    @if ($block->mobileMedia)
                        <source media="(max-width: 1023px)" srcset="{{ route('media.show', [$block->mobileMedia, 'mobile']) }}">
                    @endif
                    <img data-hero-image src="{{ route('media.show', [$image, $imageVariant]) }}" alt="{{ $image->alt_text }}" width="{{ $image->width }}" height="{{ $image->height }}" fetchpriority="high" class="h-full w-full object-cover">
                </picture>

                {{-- Gradient in front of the photo: from the hero colour at the text side, fading out toward the photo. --}}
                <div data-hero-overlay class="pointer-events-none absolute inset-0 bg-gradient-to-b from-emerald-950 via-emerald-950/60 to-transparent lg:bg-gradient-to-r lg:from-emerald-950 lg:via-emerald-950/50 lg:to-transparent"></div>
            </div>
        @endif
    </div>
</section>
