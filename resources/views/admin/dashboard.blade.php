<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Administration</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-4xl py-8 sm:px-6 lg:px-8">
        <x-card>
            <p class="text-sm text-gray-700">Manage users, website content, and reports from the links above.</p>
        </x-card>
    </div>
</x-app-layout>
