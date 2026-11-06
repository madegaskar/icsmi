<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Issue;
use Illuminate\View\View;

class ProceedingController extends Controller
{
    public function current(): View
    {
        $issue = Issue::published()->orderByDesc('published_at')->first();

        return $issue ? $this->render($issue) : view('site.proceedings.empty');
    }

    public function archives(): View
    {
        return view('site.proceedings.archives', [
            'issues' => Issue::published()->withCount(['articles' => fn ($query) => $query->published()])->orderByDesc('year')->orderByDesc('published_at')->paginate(12),
        ]);
    }

    public function show(string $slug): View
    {
        return $this->render(Issue::published()->where('slug', $slug)->firstOrFail());
    }

    private function render(Issue $issue): View
    {
        return view('site.proceedings.show', [
            'issue' => $issue,
            'articles' => Article::published()
                ->whereBelongsTo($issue)
                ->with('authors')
                ->withSum('stats as views_total', 'views')
                ->withSum('stats as downloads_total', 'downloads')
                ->orderBy('first_page')
                ->orderBy('id')
                ->paginate(25),
        ]);
    }
}
