<x-public-layout title="Confirm Submission">
    <div class="mx-auto max-w-xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold">Confirm Submission</h1>

        <dl class="mt-6 space-y-3 rounded-md bg-gray-50 p-4 text-sm">
            <div><dt class="font-medium text-gray-700">Reference</dt><dd class="mt-1 text-gray-900">{{ $submission->code }}</dd></div>
            <div><dt class="font-medium text-gray-700">Title</dt><dd class="mt-1 text-gray-900">{{ $submission->title }}</dd></div>
            <div><dt class="font-medium text-gray-700">Email</dt><dd class="mt-1 break-all text-gray-900">{{ $submission->email }}</dd></div>
        </dl>

        <form method="POST" action="{{ request()->fullUrl() }}" class="mt-6 space-y-5">
            @csrf

            @if ($hasAccount)
                <p class="text-sm text-gray-700">This submission will be added to your author account.</p>
            @else
                <p class="text-sm text-gray-700">Create a password for your author account. You will use it to follow the review and upload revisions.</p>

                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" value="Confirm Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                </div>
            @endif

            <x-primary-button>Confirm Submission</x-primary-button>
        </form>
    </div>
</x-public-layout>
