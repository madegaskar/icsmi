<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Review import</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 py-8 sm:px-6 lg:px-8">
        @if ($plan['errors'] !== [])
            <x-card>
                <h3 class="font-semibold text-red-800">This import cannot be applied yet</h3>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-700">
                    @foreach ($plan['errors'] as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        <x-card>
            <h3 class="font-semibold text-gray-800">Pages</h3>
            <x-data-table :headers="['Address', 'Title', 'Change']">
                @forelse ($plan['pages'] as $page)
                    <tr>
                        <td class="px-4 py-3 text-gray-600">/{{ $page['slug'] }}</td>
                        <td class="px-4 py-3">{{ $page['title'] }}</td>
                        <td class="px-4 py-3">{{ ucfirst($page['action']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-3 text-gray-600">No pages in this file.</td></tr>
                @endforelse
            </x-data-table>
        </x-card>

        <x-card>
            <h3 class="font-semibold text-gray-800">Announcements</h3>
            <x-data-table :headers="['Title', 'Change']">
                @forelse ($plan['announcements'] as $announcement)
                    <tr>
                        <td class="px-4 py-3">{{ $announcement['title'] }}</td>
                        <td class="px-4 py-3">{{ ucfirst($announcement['action']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-3 text-gray-600">No announcements in this file.</td></tr>
                @endforelse
            </x-data-table>
        </x-card>

        <x-card>
            <p class="text-sm text-gray-700">Menu items to replace: {{ $plan['menu_items'] }}. Home page blocks to replace: {{ $plan['home_blocks'] }}.</p>

            <div class="mt-4 flex gap-3">
                @if ($plan['errors'] === [])
                    <form method="POST" action="{{ route('admin.content.apply', $token) }}">
                        @csrf
                        <x-primary-button>Apply import</x-primary-button>
                    </form>
                @endif
                <a href="{{ route('admin.content.index') }}" class="self-center text-sm text-gray-600 underline hover:text-gray-900">Cancel</a>
            </div>
        </x-card>
    </div>
</x-app-layout>
