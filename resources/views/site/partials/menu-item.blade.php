{{-- One menu entry. Items with submenus open a small dropdown without JavaScript. --}}
@if ($item->children->isEmpty())
    <a href="{{ $item->href() }}" class="{{ $linkClass }}">{{ $item->label }}</a>
@else
    <details class="relative">
        <summary class="{{ $linkClass }} cursor-pointer list-none">{{ $item->label }}</summary>
        <div class="{{ $dropdownClass }}">
            @if ($item->href())
                <a href="{{ $item->href() }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 hover:text-emerald-800">{{ $item->label }}</a>
            @endif
            @foreach ($item->children as $child)
                <a href="{{ $child->href() }}" class="block px-3 py-2 text-gray-700 hover:bg-gray-50 hover:text-emerald-800">{{ $child->label }}</a>
            @endforeach
        </div>
    </details>
@endif
