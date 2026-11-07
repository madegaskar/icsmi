<x-public-layout title="Submission" description="Submit your paper to the International Conference on Sustainable Management and Innovation.">
    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold">Submission</h1>

        @if (session('submission_received'))
            <div class="mt-6 rounded-md bg-emerald-50 p-4 text-emerald-900">
                <p class="font-medium">Thank you. One more step: please check your email.</p>
                @if (session('submission_code'))
                    <p class="mt-1 text-sm">Your reference number is <span class="font-semibold">{{ session('submission_code') }}</span>.</p>
                @endif
                <p class="mt-1 text-sm">Open the link we sent within {{ \App\Models\PublicSubmission::CONFIRMATION_DAYS }} days to confirm and activate your author account. Your submission is reviewed only after it is confirmed.</p>
                @if (session('submission_mail_failed'))
                    <p class="mt-2 text-sm font-medium">We could not send the email right now. Please contact us and quote your reference number.</p>
                @endif
            </div>
        @else
            <form method="POST" action="{{ route('submission.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf

                <div class="hidden" aria-hidden="true">
                    <label for="website">Leave this field empty</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <x-field name="title" label="Title" required />
                <x-field name="name" label="Name" required />
                <x-field name="email" label="Email" type="email" required />
                <x-field name="phone" label="Phone" type="tel" required />

                <div>
                    <x-input-label for="institution_address" value="Institution Address" />
                    <x-text-area id="institution_address" name="institution_address" rows="3" maxlength="500" required>{{ old('institution_address') }}</x-text-area>
                    <x-input-error class="mt-2" :messages="$errors->get('institution_address')" />
                </div>

                <div>
                    <x-input-label for="abstract" value="Abstract" />
                    <x-text-area id="abstract" name="abstract" rows="8" maxlength="5000" required>{{ old('abstract') }}</x-text-area>
                    <x-input-error class="mt-2" :messages="$errors->get('abstract')" />
                </div>

                <x-field name="keywords" label="Keyword" required />

                <div>
                    <x-input-label for="file" value="Choose File (DOCX only, max 20 MB)" />
                    <input id="file" name="file" type="file" accept=".docx" required class="mt-1 block w-full text-sm text-gray-700">
                    <p class="mt-1 text-xs text-gray-500">Reviewers receive this file. Remove author names, affiliations, and acknowledgements from the text and from the document properties.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('file')" />
                </div>

                <x-primary-button>Submit</x-primary-button>
            </form>
        @endif
    </div>
</x-public-layout>
