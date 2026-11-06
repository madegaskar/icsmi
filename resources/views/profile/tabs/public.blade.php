<h2 class="text-lg font-medium text-gray-900">Public</h2>
<p class="mt-1 text-sm text-gray-600">Your ORCID iD is the public identifier for your research. Your name and affiliation are shown the way they appear below.</p>

<form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
    @csrf
    @method('patch')
    <input type="hidden" name="tab" value="public">

    <div>
        <x-input-label for="orcid" :value="__('ORCID iD (0000-0000-0000-0000)')" />
        <x-text-input id="orcid" name="orcid" type="text" class="mt-1 block w-full" :value="old('orcid', $user->orcid)" />
        <x-input-error class="mt-2" :messages="$errors->get('orcid')" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        @if (session('status') === 'profile-updated')
            <p class="text-sm text-gray-600">{{ __('Saved.') }}</p>
        @endif
    </div>
</form>

<div class="mt-8 rounded-md border border-gray-200 bg-gray-50 p-4 text-sm">
    <p class="font-semibold text-gray-900">{{ $user->name }}</p>
    @if ($user->affiliation)<p class="text-gray-700">{{ $user->affiliation }}</p>@endif
    @if ($user->country)<p class="text-gray-700">{{ $user->country }}</p>@endif
    @if ($user->orcid)<p class="mt-1 text-gray-700">ORCID: {{ $user->orcid }}</p>@endif
</div>
