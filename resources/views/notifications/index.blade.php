<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifications</h2>
            <form method="POST" action="{{ route('notifications.read') }}">
                @csrf
                <button type="submit" class="text-sm font-medium text-gray-600 underline hover:text-gray-900">Mark all as read</button>
            </form>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <ul class="divide-y divide-gray-200 rounded-lg bg-white shadow-sm">
            @forelse ($notifications as $notification)
                <li class="px-5 py-4 {{ $notification->read_at ? '' : 'bg-emerald-50/60' }}">
                    <p class="font-medium text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $notification->data['message'] ?? '' }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $notification->created_at->format('j M Y H:i') }}</p>
                    @if (! empty($notification->data['url']))
                        <a href="{{ $notification->data['url'] }}" class="mt-2 inline-block text-sm text-emerald-800 hover:underline">Open</a>
                    @endif
                </li>
            @empty
                <li class="px-5 py-6 text-sm text-gray-600">No notifications yet.</li>
            @endforelse
        </ul>

        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
