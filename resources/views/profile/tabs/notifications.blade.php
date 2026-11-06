<h2 class="text-lg font-medium text-gray-900">Notifications</h2>
<p class="mt-1 text-sm text-gray-600">The bell in the top bar always keeps your notifications. This setting only controls the email copy.</p>

<form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
    @csrf
    @method('patch')
    <input type="hidden" name="tab" value="notifications">
    <input type="hidden" name="mail_status_updates" value="0">

    <label class="flex items-center gap-3 text-sm text-gray-800">
        <input type="checkbox" name="mail_status_updates" value="1" class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500" @checked(old('mail_status_updates', $user->mail_status_updates))>
        Email me when the status of my manuscript changes
    </label>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        @if (session('status') === 'profile-updated')
            <p class="text-sm text-gray-600">{{ __('Saved.') }}</p>
        @endif
    </div>
</form>
