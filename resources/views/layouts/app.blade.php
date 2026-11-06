<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(["resources/css/app.css"])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading: one title line, with the section tabs underneath on the same grid. -->
            @isset($header)
                <header class="border-b border-gray-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:leading-snug [&_nav]:mt-3 [&_nav]:flex [&_nav]:flex-wrap [&_nav]:items-center [&_nav]:gap-x-5 [&_nav]:gap-y-2 [&_nav]:border-t [&_nav]:border-gray-100 [&_nav]:pt-3 [&_nav]:text-sm">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
