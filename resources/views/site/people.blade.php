<x-public-layout :title="$title">
    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold">{{ $title }}</h1>

        <ul class="mt-6 divide-y divide-gray-200">
            @forelse ($members as $member)
                <li class="py-4">
                    <p class="font-medium">{{ $member->name }}</p>
                    <p class="text-sm text-gray-600">{{ collect([$member->position, $member->affiliation, $member->country])->filter()->join(', ') }}</p>
                </li>
            @empty
                <li class="py-4 text-gray-600">This list has not been published yet.</li>
            @endforelse
        </ul>
    </div>
</x-public-layout>
