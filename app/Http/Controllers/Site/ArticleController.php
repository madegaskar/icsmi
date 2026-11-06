<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleStat;
use App\Support\Citation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArticleController extends Controller
{
    public function show(string $slug): View
    {
        $article = Article::published()->where('slug', $slug)->with(['authors', 'keywords', 'issue'])->firstOrFail();

        ArticleStat::recordView($article);

        return view('site.articles.show', [
            'article' => $article,
            'apa' => Citation::apa($article),
            'bibtex' => Citation::bibtex($article),
            'stats' => ArticleStat::totalsFor($article),
        ]);
    }

    public function pdf(string $slug): StreamedResponse
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();

        abort_unless($article->pdf_path && Storage::disk('local')->exists($article->pdf_path), 404);

        ArticleStat::recordDownload($article);

        return Storage::disk('local')->response($article->pdf_path, $article->slug.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function search(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'author' => ['nullable', 'string', 'max:200'],
            'keyword' => ['nullable', 'string', 'max:200'],
            'year' => ['nullable', 'integer', 'between:1900,2100'],
        ]);

        $searched = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        $articles = Article::published()
            ->with(['authors', 'issue'])
            ->when($searched['q'] ?? null, fn (Builder $query, $term) => $query->where(function (Builder $query) use ($term) {
                $query->whereRaw("title LIKE ? ESCAPE '!'", [$this->likePattern($term)])
                    ->orWhereRaw("abstract LIKE ? ESCAPE '!'", [$this->likePattern($term)]);
            }))
            ->when($searched['author'] ?? null, fn (Builder $query, $term) => $query->whereHas(
                'authors',
                fn (Builder $authors) => $authors->whereRaw("name LIKE ? ESCAPE '!'", [$this->likePattern($term)]),
            ))
            ->when($searched['keyword'] ?? null, fn (Builder $query, $term) => $query->whereHas(
                'keywords',
                fn (Builder $keywords) => $keywords->whereRaw("name LIKE ? ESCAPE '!'", [$this->likePattern($term)]),
            ))
            ->when($searched['year'] ?? null, fn (Builder $query, $year) => $query->whereHas(
                'issue',
                fn (Builder $issue) => $issue->where('year', $year),
            ))
            ->orderByDesc('published_at')
            ->paginate(20)
            ->withQueryString();

        return view('site.search', [
            'articles' => $articles,
            'filters' => $filters,
            'hasSearched' => $searched !== [],
        ]);
    }

    private function likePattern(string $term): string
    {
        return '%'.strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']).'%';
    }
}
