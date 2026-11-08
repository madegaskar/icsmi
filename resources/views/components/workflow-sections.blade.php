@props(['sections'])

<nav aria-label="Sections" class="border-b border-gray-200">
    <ul class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
        @foreach ($sections as $section)
            <li>
                <a href="#{{ $section['id'] }}" class="-mb-px inline-block border-b-2 border-transparent pb-2 text-gray-600 hover:border-emerald-800 hover:text-gray-900">{{ $section['label'] }}</a>
            </li>
        @endforeach
    </ul>
</nav>
