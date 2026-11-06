@php
    $menu = \App\Models\MenuItem::publicTree(\App\Models\MenuItem::LOCATION_HEADER);
    $banner = config('site.header_banner_uuid') ? \App\Models\Media::where('uuid', config('site.header_banner_uuid'))->first() : null;
@endphp

{{-- The banner is a box that lines up with the page content below it. The banner and navigation share one background. --}}
<header class="bg-white px-4 pt-6 sm:px-6">
    <div class="relative mx-auto max-w-6xl overflow-hidden rounded-xl bg-emerald-950 text-white shadow-sm">
        @if ($banner)
            {{-- The banner image is the whole top of the header. It carries its own text, so no words are added on top. --}}
            <picture data-header-banner class="block w-full">
                <img src="{{ route('media.show', [$banner, 'desktop']) }}" alt="{{ $banner->alt_text }}" class="block h-64 w-full object-cover">
            </picture>
        @else
            <div class="px-6 py-10 text-center sm:py-14">
                <p class="text-4xl font-bold tracking-tight sm:text-6xl">ICSMI</p>
                <p class="mt-3 text-lg font-medium sm:text-xl">International Conference on Sustainable Management and Innovation</p>
                <p class="mx-auto mt-4 max-w-3xl leading-relaxed text-emerald-200">A premier global forum for academics, researchers, industry professionals, and policymakers dedicated to advancing knowledge and practice in sustainable management, economic resilience, and organizational innovation.</p>
            </div>
        @endif

        <div class="relative border-t border-emerald-900/70">
            <div class="flex h-16 items-center justify-center px-4">
                <nav class="hidden items-center justify-center gap-6 text-sm md:flex">
                    @foreach ($menu as $item)
                        @include('site.partials.menu-item', [
                            'item' => $item,
                            'linkClass' => 'text-emerald-100 hover:text-white',
                            'dropdownClass' => 'absolute left-1/2 z-10 mt-2 w-56 -translate-x-1/2 rounded-md border border-gray-200 bg-white py-1 text-sm text-left shadow-md',
                        ])
                    @endforeach

                    @auth
                        <a href="{{ route('dashboard') }}" class="font-medium text-emerald-300 hover:text-white">Dashboard</a>
                    @endauth
                </nav>

                <details class="md:hidden">
                    <summary class="flex h-16 cursor-pointer items-center justify-center text-sm font-medium text-white">Menu</summary>

                    <nav class="absolute inset-x-0 top-16 z-10 space-y-3 border-b border-gray-200 bg-white px-4 py-4 text-gray-900 shadow-sm">
                        @foreach ($menu as $item)
                            @include('site.partials.menu-item', [
                                'item' => $item,
                                'linkClass' => 'block text-gray-700',
                                'dropdownClass' => 'mt-2 space-y-2 border-l border-gray-200 pl-3',
                            ])
                        @endforeach

                        @auth
                            <a href="{{ route('dashboard') }}" class="block font-medium text-emerald-800">Dashboard</a>
                        @endauth
                    </nav>
                </details>
            </div>
        </div>
    </div>
</header>
