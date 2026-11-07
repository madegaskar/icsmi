@if ($crumbs !== [])
    <nav aria-label="Breadcrumb" class="px-4 pt-6 text-sm text-gray-600 sm:px-6">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
            @foreach ($crumbs as $crumb)
                <li class="flex items-center gap-x-2">
                    @unless ($loop->first)
                        <span aria-hidden="true" class="text-gray-400">/</span>
                    @endunless

                    @if ($crumb['url'])
                        <a href="{{ $crumb['url'] }}" class="font-medium text-emerald-800 hover:underline">{{ $crumb['label'] }}</a>
                    @else
                        <span aria-current="page" class="text-gray-900">{{ $crumb['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
