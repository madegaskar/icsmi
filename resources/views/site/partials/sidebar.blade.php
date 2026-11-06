{{-- Right-hand column on every public page. Sections come from Admin > Sidebar. --}}
<div class="space-y-6 text-sm">
    <div class="rounded-lg bg-emerald-950 p-4 text-center">
        <a href="{{ route('submission.create') }}" class="block rounded-md bg-white px-4 py-2 font-medium text-emerald-950 hover:bg-emerald-100">Make a Submission</a>
    </div>

    @foreach (\App\Models\SidebarSection::live()->get() as $section)
        <section class="rounded-lg border border-gray-200 p-4">
            <h2 class="font-bold tracking-wide text-gray-900">{{ $section->title }}</h2>

            @switch($section->kind)
                @case(\App\Models\SidebarSection::KIND_LINKS)
                    <ul class="mt-3 space-y-2">
                        @foreach ($section->links() as $link)
                            <li><a class="text-emerald-800 hover:underline" href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                    @break

                @case(\App\Models\SidebarSection::KIND_TEXT)
                    <div class="content mt-3 text-gray-700 [&>*+*]:!mt-2">{{ $section->bodyHtml() }}</div>
                    @break

                @case(\App\Models\SidebarSection::KIND_VISITOR)
                    @php($visitors = app(\App\Services\VisitCounter::class)->summary())
                    <p class="mt-3 font-semibold text-gray-900">Total visits: {{ $visitors['total'] }}</p>
                    @if ($visitors['countries'] === [])
                        <p class="mt-2 text-gray-600">No visits recorded yet.</p>
                    @else
                        <ul class="mt-3 space-y-2">
                            @foreach ($visitors['countries'] as $country)
                                <li class="flex items-center justify-between gap-2">
                                    <span><span aria-hidden="true">{{ $country['flag'] }}</span> {{ $country['name'] }}</span>
                                    <span class="tabular-nums text-gray-700">{{ $country['count'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @break
            @endswitch
        </section>
    @endforeach
</div>
