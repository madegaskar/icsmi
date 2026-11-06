<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Profile') }}</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="flex flex-wrap gap-2 border-b border-gray-200 pb-3 text-sm">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('profile.edit', ['tab' => $key]) }}"
                   class="rounded-md px-3 py-1.5 font-medium {{ $activeTab === $key ? 'bg-gray-800 text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="mt-6 rounded-lg bg-white p-6 shadow-sm">
            @include('profile.tabs.'.$activeTab, ['user' => $user])
        </div>
    </div>
</x-app-layout>
