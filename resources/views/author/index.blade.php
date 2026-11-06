<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Manuscripts</h2>
            <a href="{{ route('author.manuscripts.create') }}" class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">Submit Manuscript</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl py-8 sm:px-6 lg:px-8">
        @include('manuscripts.table', ['showRoute' => 'author.manuscripts.show'])
    </div>
</x-app-layout>
