<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="mx-auto max-w-4xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <p class="text-gray-700">Welcome, {{ Auth::user()->name }}.</p>

            <ul class="mt-4 space-y-2 text-sm">
                @foreach (Auth::user()->roles as $assigned)
                    <li><a class="font-medium text-emerald-800 hover:underline" href="{{ route($assigned->role->workspaceRoute()) }}">{{ $assigned->role->label() }} workspace</a></li>
                @endforeach
            </ul>
        </x-card>
    </div>
</x-app-layout>
