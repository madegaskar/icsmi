@props(['name', 'label', 'checked' => false])

<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
    {{ $label }}
</label>
