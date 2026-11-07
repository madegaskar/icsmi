<nav class="relative bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="text-xl text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @foreach (Auth::user()->roles as $assigned)
                        <x-nav-link :href="route($assigned->role->workspaceRoute())" :active="request()->routeIs($assigned->role->workspaceRoute())">
                            {{ $assigned->role->label() }}
                        </x-nav-link>
                    @endforeach

                    @if (Auth::user()->hasRole(\App\Enums\Role::Admin, \App\Enums\Role::Editor))
                        <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">Reports</x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden items-center gap-4 sm:flex">
                @include('site.partials.notification-bell', ['tone' => 'dark'])

                <a href="{{ route('profile.edit') }}" class="flex items-center text-sm font-medium leading-none text-gray-500 hover:text-gray-700">{{ Auth::user()->name }}</a>

                <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                    @csrf
                    <button type="submit" class="flex items-center text-sm font-medium leading-none text-gray-500 hover:text-gray-700">{{ __('Log Out') }}</button>
                </form>
            </div>

            <details class="flex items-center sm:hidden">
                <summary class="flex h-16 cursor-pointer items-center text-sm font-medium text-gray-600">{{ __('Menu') }}</summary>

                <div class="absolute inset-x-0 top-16 z-10 space-y-3 border-b border-gray-200 bg-white px-4 py-4 shadow-sm">
                    <a href="{{ route('dashboard') }}" class="block text-gray-700">{{ __('Dashboard') }}</a>

                    @foreach (Auth::user()->roles as $assigned)
                        <a href="{{ route($assigned->role->workspaceRoute()) }}" class="block text-gray-700">{{ $assigned->role->label() }}</a>
                    @endforeach

                    @if (Auth::user()->hasRole(\App\Enums\Role::Admin, \App\Enums\Role::Editor))
                        <a href="{{ route('reports.index') }}" class="block text-gray-700">Reports</a>
                    @endif

                    <div class="border-t border-gray-200 pt-3">
                        <div class="font-medium text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="block text-gray-700">{{ __('Profile') }}</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-700">{{ __('Log Out') }}</button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</nav>
