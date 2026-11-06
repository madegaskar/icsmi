<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manuscripts</h2>
        @include('editor.partials.tabs')
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('editor.dashboard') }}" class="flex flex-wrap items-end gap-3 px-4 sm:px-0">
            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All</option>
                    @foreach (\App\Enums\ManuscriptStatus::cases() as $option)
                        <option value="{{ $option->value }}" @selected($status === $option->value)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <x-primary-button>Filter</x-primary-button>
        </form>

        @include('manuscripts.table', ['showRoute' => 'editor.manuscripts.show'])
    </div>
</x-app-layout>
