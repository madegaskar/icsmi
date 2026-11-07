@props(['title' => null, 'description' => null])

@php
    $crumbs = \App\Support\Breadcrumbs::for(request(), $title);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' | ICSMI' : 'ICSMI | International Conference on Sustainable Management and Innovation' }}</title>

        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif

        {{ $head ?? '' }}

        @vite(["resources/css/app.css"])
    </head>
    <body class="flex min-h-screen flex-col bg-white font-sans text-gray-900 antialiased">
        @include('site.partials.header')

        <main class="w-full flex-1">
            {{-- Every public page shares the same two columns: the page on the left, the sidebar on the right. --}}
            <div class="mx-auto grid max-w-6xl lg:grid-cols-[minmax(0,1fr)_18rem]">
                <div class="min-w-0">
                    @include('site.partials.breadcrumb')

                    {{ $slot }}
                </div>

                <aside class="px-4 pb-12 sm:px-6 lg:py-12 lg:pe-6 lg:ps-0">
                    @include('site.partials.sidebar')
                </aside>
            </div>
        </main>

        @include('site.partials.footer')
    </body>
</html>
