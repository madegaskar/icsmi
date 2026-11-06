{{-- Login, register, and password pages use the same public layout as the rest of the site. --}}
@php
    $heading = match (true) {
        request()->routeIs('login') => 'Log in',
        request()->routeIs('register') => 'Create an account',
        request()->routeIs('password.request') => 'Reset your password',
        request()->routeIs('password.reset') => 'Choose a new password',
        request()->routeIs('password.confirm') => 'Confirm your password',
        request()->routeIs('verification.notice') => 'Verify your email',
        default => 'Account',
    };
@endphp

<x-public-layout :title="$heading">
    <div class="w-full max-w-2xl px-4 py-8 sm:px-6">
        <h1 class="text-2xl font-bold">{{ $heading }}</h1>

        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            {{ $slot }}
        </div>
    </div>
</x-public-layout>
