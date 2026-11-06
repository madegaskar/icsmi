<div class="mt-4">
    <x-input-label for="affiliation" :value="__('Affiliation')" />
    <x-text-input id="affiliation" class="block mt-1 w-full" type="text" name="affiliation" :value="old('affiliation', $user?->affiliation)" :required="$user === null" autocomplete="organization" />
    <x-input-error :messages="$errors->get('affiliation')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="country" :value="__('Country')" />
    <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country', $user?->country)" autocomplete="country-name" />
    <x-input-error :messages="$errors->get('country')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="orcid" :value="__('ORCID (optional)')" />
    <x-text-input id="orcid" class="block mt-1 w-full" type="text" name="orcid" :value="old('orcid', $user?->orcid)" placeholder="0000-0000-0000-0000" />
    <x-input-error :messages="$errors->get('orcid')" class="mt-2" />
</div>
