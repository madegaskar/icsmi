<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-8">
        @csrf

        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold uppercase tracking-wide text-gray-700">Profile</legend>

            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="affiliation" :value="__('Affiliation')" />
                <x-text-input id="affiliation" class="block mt-1 w-full" type="text" name="affiliation" :value="old('affiliation')" required autocomplete="organization" />
                <x-input-error :messages="$errors->get('affiliation')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="country" :value="__('Country')" />
                <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country')" autocomplete="country-name" />
                <x-input-error :messages="$errors->get('country')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="orcid" :value="__('ORCID iD (optional)')" />
                <x-text-input id="orcid" class="block mt-1 w-full" type="text" name="orcid" :value="old('orcid')" placeholder="0000-0000-0000-0000" />
                <x-input-error :messages="$errors->get('orcid')" class="mt-2" />
            </div>
        </fieldset>

        <fieldset class="space-y-4">
            <legend class="text-sm font-semibold uppercase tracking-wide text-gray-700">Login</legend>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Repeat password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </fieldset>

        <fieldset class="space-y-3">
            <legend class="text-sm font-semibold uppercase tracking-wide text-gray-700">Consent</legend>

            <label class="flex items-start gap-3 text-sm text-gray-800">
                <input type="checkbox" name="privacy_consent" value="1" required class="mt-1 rounded border-gray-300 text-emerald-700 focus:ring-emerald-500" @checked(old('privacy_consent'))>
                <span>Yes, I agree to have my data collected and stored according to the <a href="{{ route('page', 'privacy-statement') }}" class="underline text-emerald-800">privacy statement</a>.</span>
            </label>
            <x-input-error :messages="$errors->get('privacy_consent')" />

            <label class="flex items-start gap-3 text-sm text-gray-800">
                <input type="checkbox" name="mail_announcements" value="1" class="mt-1 rounded border-gray-300 text-emerald-700 focus:ring-emerald-500" @checked(old('mail_announcements'))>
                <span>Yes, I would like to be notified of new publications and announcements.</span>
            </label>
        </fieldset>

        <div class="flex items-center justify-between">
            <a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
            <x-primary-button>{{ __('Register') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
