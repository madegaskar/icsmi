{{-- Bell with unread count and the five most recent notifications. $tone is "light" on dark bars and "dark" on white bars. --}}
@php
    $unreadCount = Auth::user()->unreadNotifications()->count();
    $recentNotifications = Auth::user()->notifications()->latest()->limit(5)->get();
    $bellTone = ($tone ?? 'dark') === 'light' ? 'text-emerald-100 hover:text-white' : 'text-gray-500 hover:text-gray-700';
@endphp

<details class="relative">
    <summary data-unread-count="{{ $unreadCount }}" aria-label="Notifications" class="relative flex cursor-pointer list-none items-center {{ $bellTone }} [&::-webkit-details-marker]:hidden">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold text-white">{{ $unreadCount }}</span>
        @endif
    </summary>

    <div class="absolute right-0 z-20 mt-3 w-80 overflow-hidden rounded-lg border border-gray-200 bg-white text-left text-gray-900 shadow-lg">
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
