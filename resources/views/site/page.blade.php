@php
    $isContact = $page->slug === 'contact';
    $isScope = in_array($page->slug, ['scope', 'peer-review-process', 'abstracting-and-indexing'], true);
    $scopeSections = $isScope ? \App\Support\MarkdownSections::parse((string) $page->body) : collect();
    $sections = collect(preg_split('/^## /m', (string) $page->body, -1, PREG_SPLIT_NO_EMPTY))
        ->map(function (string $part) {
            $lines = explode("\n", $part, 2);

            return ['title' => trim($lines[0]), 'body' => $lines[1] ?? ''];
        })
        ->values();
    $intro = $isContact ? $sections->first() : null;
    $columns = $isContact ? $sections->slice(1)->values() : collect();
@endphp

<x-public-layout :title="$page->title">
    @if ($preview ?? false)
        <x-slot:head>
            <meta name="robots" content="noindex, nofollow">
        </x-slot:head>
    @endif

    @if ($preview ?? false)
        <div class="bg-amber-50 px-4 py-2 text-center text-sm text-amber-900">
            Preview. This page is {{ $page->state() === \App\Models\Page::STATE_PUBLISHED ? 'published' : ($page->state() === \App\Models\Page::STATE_SCHEDULED ? 'scheduled' : 'a draft') }} and only staff can see this view.
        </div>
    @endif

    @if ($isScope)
        <article class="max-w-4xl px-4 py-12 sm:px-6">
            <div class="rounded-lg bg-emerald-950 px-6 py-6 text-white">
                <h1 class="text-2xl font-bold">{{ $page->title }}</h1>
                <p class="mt-1 text-sm text-emerald-200">International Conference on Sustainable Management and Innovation (ICSMI)</p>
            </div>

            @foreach ($scopeSections as $section)
                <section class="mt-6 rounded-lg border border-gray-200 bg-white p-6">
                    @if ($section['title'] !== '')
                        <h2 class="text-lg font-semibold text-emerald-900">{{ $section['title'] }}</h2>
                    @endif

                    @if ($section['title'] === 'Peer Review Workflow')
                        @php(preg_match_all('/^\d+\.\s*(.+)$/m', $section['intro'], $steps))
                        <ol class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6" aria-label="Peer review workflow diagram">
                            @foreach ($steps[1] as $index => $step)
                                <li class="flex flex-col items-center rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-center">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-900 text-sm font-bold text-white">{{ $index + 1 }}</span>
                                    <p class="mt-3 text-sm font-semibold text-emerald-950">{{ $step }}</p>
                                </li>
                            @endforeach
                        </ol>
                    @elseif ($section['intro'] !== '')
                        <div class="content mt-3">{{ \App\Support\SafeMarkdown::render($section['intro']) }}</div>
                    @endif

                    @if ($section['subsections']->isNotEmpty())
                        <div class="mt-5 grid gap-4 {{ $section['subsections']->count() > 1 ? 'md:grid-cols-2' : '' }}">
                            @foreach ($section['subsections'] as $subsection)
                                <div class="rounded-md border-l-4 border-emerald-700 bg-gray-50 p-4">
                                    <h3 class="font-semibold text-emerald-900">{{ $subsection['title'] }}</h3>
                                    <div class="content mt-2 text-sm [&>*+*]:!mt-1">{{ \App\Support\SafeMarkdown::render($subsection['body']) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </article>
    @elseif ($isContact)
        <article class="max-w-4xl px-4 py-12 sm:px-6">
            <h1 class="text-3xl font-bold">{{ $page->title }}</h1>

            @if ($intro)
                <div class="mt-6">@include('site.partials.contact-section', ['section' => $intro])</div>
            @endif

            <div class="mt-6 grid gap-10 md:grid-cols-2">
                @foreach ($columns as $section)
                    @include('site.partials.contact-section', ['section' => $section])
                @endforeach
            </div>
        </article>
    @else
        <article class="max-w-3xl px-4 py-12 sm:px-6">
            <h1 class="text-3xl font-bold">{{ $page->title }}</h1>
            <div class="content mt-6">{{ $page->bodyHtml() }}</div>
        </article>
    @endif
</x-public-layout>
