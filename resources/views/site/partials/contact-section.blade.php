{{-- One titled block on the contact page. Paragraph spacing is tighter than in regular page text. --}}
<section>
    @if ($section['title'] !== '')
        <h2 class="text-xl font-semibold">{{ $section['title'] }}</h2>
    @endif

    <div class="content mt-2 [&>*+*]:!mt-2">{{ \App\Support\SafeMarkdown::render($section['body']) }}</div>
</section>
