@php
    $menu = \App\Models\MenuItem::publicTree(\App\Models\MenuItem::LOCATION_HEADER);
    $banner = config('site.header_banner_uuid') ? \App\Models\Media::where('uuid', config('site.header_banner_uuid'))->first() : null;
    $authPaths = ['/login', '/register'];
    $authActions = $menu->filter(fn ($item) => in_array($item->href(), $authPaths, true))->values();
    $navItems = $menu->reject(fn ($item) => in_array($item->href(), $authPaths, true))->values();
@endphp

{{-- The banner is a box that lines up with the page content below it. The banner and navigation share one background. --}}
<header class="bg-white px-4 pt-6 sm:px-6">
    <div class="relative mx-auto max-w-6xl rounded-xl bg-emerald-950 text-white shadow-sm">
        @if ($banner)
            {{-- The banner image is the whole top of the header. It carries its own text, so no words are added on top. --}}
            <picture data-header-banner class="block w-full overflow-hidden rounded-t-xl">
                <img src="{{ route('media.show', [$banner, 'desktop']) }}" alt="{{ $banner->alt_text }}" class="block h-auto w-full object-contain sm:h-64 sm:object-cover">
            </picture>
        @else
            <div class="px-6 py-10 text-center sm:py-14">
                <p class="text-4xl font-bold tracking-tight sm:text-6xl">ICSMI</p>
                <p class="mt-3 text-lg font-medium sm:text-xl">International Conference on Sustainable Management and Innovation</p>
                <p class="mx-auto mt-4 max-w-3xl leading-relaxed text-emerald-200">A premier global forum for academics, researchers, industry professionals, and policymakers dedicated to advancing knowledge and practice in sustainable management, economic resilience, and organizational innovation.</p>
            </div>
        @endif

        <div class="relative border-t border-emerald-900/70">
            {{-- Desktop: menu in the middle, Register and Login as buttons (or the account controls) on the right. --}}
            <nav class="hidden h-16 items-center gap-6 px-4 text-sm md:flex">
                <div class="flex-1"></div>

                <div class="flex items-center justify-center gap-6">
                    @foreach ($navItems as $item)
                        @include('site.partials.menu-item', [
                            'item' => $item,
                            'linkClass' => 'text-emerald-100 hover:text-white',
                            'dropdownClass' => 'absolute left-1/2 z-10 mt-2 w-56 -translate-x-1/2 rounded-md border border-gray-200 bg-white py-1 text-sm text-left text-gray-800 shadow-md',
                        ])
                    @endforeach
                </div>

                <div class="flex flex-1 justify-end">
                    @auth
                        @include('site.partials.account-controls')
                    @else
                        <div data-desktop-auth-actions class="flex items-center gap-2">
                            @foreach ($authActions as $item)
                                @if ($item->href() === '/register')
                                    <a href="{{ $item->href() }}" class="rounded-md bg-white px-4 py-2 font-semibold text-emerald-950 hover:bg-emerald-50">{{ $item->label }}</a>
                                @else
                                    <a href="{{ $item->href() }}" class="rounded-md border border-white/70 px-4 py-2 font-semibold text-white hover:bg-white/10">{{ $item->label }}</a>
                                @endif
                            @endforeach
                        </div>
                    @endauth
                </div>
            </nav>

            {{-- Phones: one toggle holds the whole menu. Login and Register sit on the right as buttons; everything else sits on the left. --}}
            <details class="group md:hidden" data-mobile-toggle>
                <summary class="flex h-16 cursor-pointer list-none items-center justify-between px-4 text-sm font-medium text-white">
                    <span>Menu</span>
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </summary>

                <nav class="space-y-3 border-t border-emerald-900/70 bg-white px-4 py-4 text-left text-gray-900 rounded-b-xl">
                    @foreach ($navItems as $item)
                        @include('site.partials.menu-item', [
                            'item' => $item,
                            'linkClass' => 'block text-gray-700',
                            'dropdownClass' => 'mt-2 space-y-2 border-l border-gray-200 pl-3',
                        ])
                    @endforeach

                    @auth
                        <div class="space-y-2 border-t border-gray-200 pt-3">
                            <p class="font-medium text-gray-800">{{ Auth::user()->name }}</p>
                            <a href="{{ route('dashboard') }}" class="block text-gray-700">Dashboard</a>
                            <a href="{{ route('profile.edit') }}" class="block text-gray-700">Profile</a>
                            <a href="{{ route('notifications.index') }}" class="block text-gray-700">Notifications</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-md border border-emerald-800 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Log out</button>
                            </form>
                        </div>
                    @else
                        <div data-mobile-auth-actions class="flex justify-end gap-2 border-t border-gray-200 pt-3">
                            @foreach ($authActions as $item)
                                @if ($item->href() === '/register')
                                    <a href="{{ $item->href() }}" class="rounded-md bg-emerald-800 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-900">{{ $item->label }}</a>
                                @else
                                    <a href="{{ $item->href() }}" class="rounded-md border border-emerald-800 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">{{ $item->label }}</a>
                                @endif
                            @endforeach
                        </div>
                    @endauth
                </nav>
            </details>
        </div>
    </div>
</header>
