<div class="mt-4">
    <x-input-label for="blinded_file" value="Anonymized manuscript (PDF or DOCX, max 20 MB)" />
    <input id="blinded_file" name="blinded_file" type="file" accept=".pdf,.docx" required class="mt-1 block w-full text-sm text-gray-700">
    <p class="mt-1 text-xs text-gray-500">Reviewers receive this file. Remove author names, affiliations, and acknowledgements from the text and from the document properties.</p>
    <x-input-error class="mt-2" :messages="$errors->get('blinded_file')" />
</div>

<div class="mt-4">
    <x-input-label for="full_file" value="Full version with author details (optional)" />
    <input id="full_file" name="full_file" type="file" accept=".pdf,.docx" class="mt-1 block w-full text-sm text-gray-700">
    <p class="mt-1 text-xs text-gray-500">Only you and the editors can open this file.</p>
    <x-input-error class="mt-2" :messages="$errors->get('full_file')" />
</div>
