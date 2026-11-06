<h2 class="text-lg font-medium text-gray-900">Identity</h2>
<p class="mt-1 text-sm text-gray-600">Your name and where you work. These appear on your submissions.</p>

<form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
    @csrf
    @method('patch')
    <input type="hidden" name="tab" value="identity">

    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="affiliation" :value="__('Affiliation')" />
        <x-text-input id="affiliation" name="affiliation" type="text" class="mt-1 block w-full" :value="old('affiliation', $user->affiliation)" />
        <x-input-error class="mt-2" :messages="$errors->get('affiliation')" />
    </div>

    <div>
        <x-input-label for="country" :value="__('Country')" />
        <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="old('country', $user->country)" />
        <x-input-error class="mt-2" :messages="$errors->get('country')" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        @if (session('status') === 'profile-updated')
            <p class="text-sm text-gray-600">{{ __('Saved.') }}</p>
        @endif
    </div>
</form>
