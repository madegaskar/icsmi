<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>

        @vite(["resources/css/app.css"])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <main class="min-h-screen flex flex-col items-center justify-center gap-2 bg-gray-100 px-4 text-center">
            <p class="text-3xl font-bold text-gray-800">@yield('code')</p>
            <p class="text-gray-600">@yield('message')</p>
            <a href="{{ url('/') }}" class="mt-2 text-sm underline text-gray-600 hover:text-gray-900">ICSMI</a>
        </main>
    </body>
</html>
