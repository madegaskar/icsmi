<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc></url>
    <url><loc>{{ route('proceedings.archives') }}</loc></url>
    <url><loc>{{ route('editorial-board') }}</loc></url>
    <url><loc>{{ route('speakers') }}</loc></url>
    <url><loc>{{ route('announcements') }}</loc></url>
    <url><loc>{{ route('submission.create') }}</loc></url>
    @foreach ($pages as $page)
        <url><loc>{{ route('page', $page->slug) }}</loc><lastmod>{{ $page->updated_at->toDateString() }}</lastmod></url>
    @endforeach
    @foreach ($issues as $issue)
        <url><loc>{{ route('proceedings.show', $issue->slug) }}</loc><lastmod>{{ $issue->updated_at->toDateString() }}</lastmod></url>
    @endforeach
    @foreach ($articles as $article)
        <url><loc>{{ route('articles.show', $article->slug) }}</loc><lastmod>{{ $article->updated_at->toDateString() }}</lastmod></url>
    @endforeach
</urlset>
