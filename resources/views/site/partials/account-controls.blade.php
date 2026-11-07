{{-- Desktop controls for a signed-in user on the dark header: name menu, bell, and log out. --}}
<div class="flex items-center gap-4 text-sm">
    @include('site.partials.notification-bell', ['tone' => 'light'])

    <details class="relative">
        <summary data-account-menu class="flex cursor-pointer list-none items-center gap-1 font-medium text-white hover:text-emerald-200 [&::-webkit-details-marker]:hidden">
            <span>{{ Auth::user()->name }}</span>
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </summary>

        <div class="absolute right-0 z-20 mt-3 w-56 rounded-md border border-gray-200 bg-white py-1 text-left text-gray-800 shadow-lg">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-gray-50 hover:text-emerald-800">Dashboard</a>

            @foreach (Auth::user()->roles as $assigned)
                <a href="{{ route($assigned->role->workspaceRoute()) }}" class="block px-4 py-2 hover:bg-gray-50 hover:text-emerald-800">{{ $assigned->role->label() }}</a>
            @endforeach

            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50 hover:text-emerald-800">Profile</a>
            <a href="{{ route('notifications.index') }}" class="block px-4 py-2 hover:bg-gray-50 hover:text-emerald-800">Notifications</a>
        </div>
    </details>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="rounded-md border border-white/70 px-3 py-1.5 font-semibold text-white hover:bg-white/10">Log out</button>
    </form>
</div>
