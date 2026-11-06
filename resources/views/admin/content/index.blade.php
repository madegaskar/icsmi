<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Content</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto grid max-w-5xl gap-6 py-8 sm:px-6 lg:grid-cols-2 lg:px-8">
        <x-card>
            <h3 class="font-semibold text-gray-800">Export</h3>
            <p class="mt-2 text-sm text-gray-600">Download pages, announcements, the menu, the home page, and a list of library files as one JSON file. Accounts and passwords are never included.</p>
            <a href="{{ route('admin.content.export') }}" class="mt-4 inline-block rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">Download JSON</a>
        </x-card>

        <x-card>
            <h3 class="font-semibold text-gray-800">Import</h3>
            <p class="mt-2 text-sm text-gray-600">Upload a file made by Export (up to 2 MB). You will see a preview first. Nothing changes until you confirm. The menu and the home page are replaced by the file's contents.</p>

            <form method="POST" action="{{ route('admin.content.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                @csrf
                <input type="file" name="file" accept=".json,application/json" required class="block w-full text-sm text-gray-700">
                <x-input-error :messages="$errors->get('file')" />
                <x-primary-button>Preview import</x-primary-button>
            </form>

            @if ($errors->has('content'))
                <ul class="mt-4 list-disc space-y-1 pl-5 text-sm text-red-700">
                    @foreach ($errors->get('content') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
</x-app-layout>
