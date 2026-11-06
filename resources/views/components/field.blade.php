@props(['name', 'label', 'value' => null, 'type' => 'text', 'required' => false])

<div {{ $attributes->only('class') }}>
    <x-input-label :for="$name" :value="$label" />
    <x-text-input :id="$name" :name="$name" :type="$type" class="mt-1 block w-full" :value="old($name, $value)" :required="$required" />
    <x-input-error class="mt-2" :messages="$errors->get($name)" />
</div>
