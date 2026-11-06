@php
    $columns = \App\Models\MenuItem::publicTree(\App\Models\MenuItem::LOCATION_FOOTER);
@endphp

<footer class="border-t border-gray-200 bg-gray-50 text-sm text-gray-600">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-3 sm:px-6">
        @foreach ($columns as $column)
            <div>
                @if ($column->href())
                    <p><a class="font-semibold text-gray-900 hover:text-emerald-800" href="{{ $column->href() }}">{{ $column->label }}</a></p>
                @else
                    <p class="font-semibold text-gray-900">{{ $column->label }}</p>
                @endif

                @if ($column->children->isNotEmpty())
                    <ul class="mt-3 space-y-2">
                        @foreach ($column->children as $child)
                            <li><a class="hover:text-emerald-800" href="{{ $child->href() }}">{{ $child->label }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>
    <p class="mx-auto max-w-6xl px-4 pb-10 sm:px-6">International Conference on Sustainable Management and Innovation</p>
</footer>
