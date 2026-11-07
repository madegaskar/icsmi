<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settings</h2>
        @include('admin.partials-tabs')
    </x-slot>

    <div class="mx-auto max-w-3xl py-8 sm:px-6 lg:px-8">
        <x-flash-status />

        <x-card>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <p class="text-sm font-medium text-gray-900">New account registration</p>
                    <p class="mt-1 text-sm text-gray-600">
                        When closed, the Register button is hidden and the registration page sends visitors to the login page. Existing accounts can still log in.
                    </p>

                    <div class="mt-3 flex items-center gap-6">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="radio" name="registration_open" value="1" class="text-emerald-800 focus:ring-emerald-700" @checked($registrationOpen)>
                            Open
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="radio" name="registration_open" value="0" class="text-emerald-800 focus:ring-emerald-700" @checked(! $registrationOpen)>
                            Closed
                        </label>
                    </div>
                </div>

                <x-primary-button>Save</x-primary-button>
            </form>
        </x-card>
    </div>
</x-app-layout>
