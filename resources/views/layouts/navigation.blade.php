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
                @php($unreadCount = Auth::user()->unreadNotifications()->count())
                @php($recentNotifications = Auth::user()->notifications()->latest()->limit(5)->get())
                <details class="relative">
                    <summary data-unread-count="{{ $unreadCount }}" aria-label="Notifications" class="relative flex cursor-pointer list-none items-center text-gray-500 hover:text-gray-700 [&::-webkit-details-marker]:hidden">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        @if ($unreadCount > 0)
                            <span class="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold text-white">{{ $unreadCount }}</span>
                        @endif
                    </summary>

                    <div class="absolute right-0 z-20 mt-3 w-80 overflow-hidden rounded-lg border border-gray-200 bg-white text-left shadow-lg">
                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                            <p class="text-sm font-semibold text-gray-900">Notifications</p>
                            @if ($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.read') }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-emerald-800 hover:underline">Mark all as read</button>
                                </form>
                            @endif
                        </div>

                        <ul class="max-h-96 divide-y divide-gray-100 overflow-y-auto">
                            @forelse ($recentNotifications as $notification)
                                <li class="px-4 py-3 {{ $notification->read_at ? '' : 'bg-emerald-50/60' }}">
                                    @if (! empty($notification->data['url']))
                                        <a href="{{ $notification->data['url'] }}" class="block">
                                    @endif
                                        <p class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                        <p class="mt-0.5 line-clamp-2 text-xs text-gray-600">{{ $notification->data['message'] ?? '' }}</p>
                                        <p class="mt-1 text-[11px] text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                    @if (! empty($notification->data['url']))
                                        </a>
                                    @endif
                                </li>
                            @empty
                                <li class="px-4 py-6 text-center text-sm text-gray-600">No notifications yet.</li>
                            @endforelse
                        </ul>

                        <div class="border-t border-gray-100 px-4 py-2.5 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-emerald-800 hover:underline">View all notifications</a>
                        </div>
                    </div>
                </details>

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
