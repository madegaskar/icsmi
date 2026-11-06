@props(['title' => null])

<section {{ $attributes->merge(['class' => 'bg-white p-4 shadow-sm sm:rounded-lg sm:p-6']) }}>
    @if ($title)
        <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
    @endif
    {{ $slot }}
</section>
