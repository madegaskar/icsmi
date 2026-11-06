<h2 class="text-lg font-medium text-gray-900">Contact</h2>
<p class="mt-1 text-sm text-gray-600">How the editorial office reaches you.</p>

<form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
    @csrf
    @method('patch')
    <input type="hidden" name="tab" value="contact">

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
        <p class="mt-1 text-xs text-gray-500">Changing your email means verifying the new address again.</p>
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        @if (session('status') === 'profile-updated')
            <p class="text-sm text-gray-600">{{ __('Saved.') }}</p>
        @endif
    </div>
</form>

@if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
    <form method="post" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <p class="text-sm text-gray-800">Your email address is not verified yet.</p>
        <button type="submit" class="mt-2 text-sm underline text-gray-600 hover:text-gray-900">Send the verification email again</button>
    </form>
@endif
