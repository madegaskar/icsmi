<?php

namespace App\Http\Controllers\Editorial;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManuscriptRequest;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Issue;
use App\Rules\ManuscriptDocument;
use App\Services\ArticlePublisher;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return view('editor.articles.index', [
            'articles' => Article::with('issue:id,title')->latest('id')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Article);
    }

    public function store(Request $request, ArticlePublisher $publisher): RedirectResponse
    {
        $data = $this->validated($request);

        $article = DB::transaction(function () use ($request, $data, $publisher) {
            $article = Article::create($this->attributes($data, $request) + [
                'slug' => UniqueSlug::for(Article::class, $data['title']),
                'pdf_path' => $request->hasFile('pdf') ? $publisher->storePdf($request->file('pdf')) : null,
            ]);

            $this->syncRelations($article, $data, $publisher);

            return $article;
        });

        AuditLog::record('article.created', $request->user()->id, ['article_id' => $article->id]);

        return redirect()->route('editor.articles.index')->with('status', 'Article saved.');
    }

    public function edit(Article $article): View
    {
        return $this->form($article->load(['authors', 'keywords']));
    }

    public function update(Request $request, Article $article, ArticlePublisher $publisher): RedirectResponse
    {
        $data = $this->validated($request, $article);
        $previousPdf = $article->pdf_path;

        DB::transaction(function () use ($request, $article, $data, $publisher) {
            $attributes = $this->attributes($data, $request, $article);

            if ($request->hasFile('pdf')) {
                $attributes['pdf_path'] = $publisher->storePdf($request->file('pdf'));
            }

            $article->update($attributes);
            $this->syncRelations($article, $data, $publisher);
        });

        if ($previousPdf && $previousPdf !== $article->pdf_path) {
            Storage::disk('local')->delete($previousPdf);
        }

        AuditLog::record('article.updated', $request->user()->id, ['article_id' => $article->id]);

        return redirect()->route('editor.articles.index')->with('status', 'Article saved.');
    }

    private function form(Article $article): View
    {
        return view('editor.articles.form', [
            'article' => $article,
            'issues' => Issue::orderByDesc('year')->orderByDesc('id')->get(['id', 'title']),
            'maxAuthors' => StoreManuscriptRequest::MAX_AUTHORS,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Article $article = null): array
    {
        $authors = $request->input('authors');

        if (is_array($authors)) {
            $request->merge(['authors' => array_values(array_filter($authors, fn ($author) => is_array($author) && filled($author['name'] ?? null)))]);
        }

        return $request->validate([
            'issue_id' => ['required', 'integer', Rule::exists('issues', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'max:5000'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'doi' => ['nullable', 'string', 'max:255', 'regex:/^10\.\d{4,9}\/\S+$/', Rule::unique('articles', 'doi')->ignore($article?->id)],
            'reference_list' => ['nullable', 'string', 'max:20000'],
            'first_page' => ['nullable', 'integer', 'min:1', 'required_with:last_page'],
            'last_page' => ['nullable', 'integer', 'gte:first_page'],
            'pdf' => ['nullable', 'file', 'max:20480', new ManuscriptDocument(['pdf'])],
            'published' => ['nullable', 'boolean'],
            'authors' => ['required', 'array', 'min:1', 'max:'.StoreManuscriptRequest::MAX_AUTHORS],
            'authors.*.name' => ['required', 'string', 'max:255'],
            'authors.*.affiliation' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data, Request $request, ?Article $article = null): array
    {
        return [
            'issue_id' => $data['issue_id'],
            'title' => $data['title'],
            'abstract' => $data['abstract'],
            'doi' => $data['doi'] ?? null,
            'reference_list' => $data['reference_list'] ?? null,
            'first_page' => $data['first_page'] ?? null,
            'last_page' => $data['last_page'] ?? null,
            'published_at' => $request->boolean('published') ? ($article?->published_at ?? now()) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncRelations(Article $article, array $data, ArticlePublisher $publisher): void
    {
        $article->authors()->delete();

        foreach ($data['authors'] as $position => $author) {
            $article->authors()->create(['name' => $author['name'], 'affiliation' => $author['affiliation'] ?? null, 'position' => $position]);
        }

        $publisher->syncKeywords($article, $data['keywords'] ?? '');
    }
}
