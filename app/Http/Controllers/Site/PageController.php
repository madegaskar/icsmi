<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\BoardMember;
use App\Models\HomeBlock;
use App\Models\Issue;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $latest = Issue::published()->orderByDesc('published_at')->first();

        return view('site.home', [
            'blocks' => HomeBlock::live()->get(),
            'latest' => $latest,
            'articles' => $latest
                ? Article::published()->whereBelongsTo($latest)->with('authors')->orderBy('first_page')->limit(5)->get()
                : collect(),
            'announcements' => Announcement::published()->orderByDesc('published_at')->limit(3)->get(),
        ]);
    }

    public function show(Page $page): View
    {
        abort_unless($page->isPublished(), 404);

        return view('site.page', ['page' => $page, 'preview' => false]);
    }

    public function editorialBoard(): View
    {
        return view('site.people', [
            'title' => 'Editorial Board',
            'members' => BoardMember::where('group', BoardMember::GROUP_EDITORIAL)->orderBy('sort_order')->get(),
        ]);
    }

    public function speakers(): View
    {
        return view('site.people', [
            'title' => 'Speakers',
            'members' => BoardMember::where('group', BoardMember::GROUP_SPEAKER)->orderBy('sort_order')->get(),
        ]);
    }

    public function announcements(Request $request): View
    {
        $category = $request->query('category');

        abort_if($category !== null && ! in_array($category, Announcement::CATEGORIES, true), 404);

        $query = Announcement::published()->orderByDesc('published_at');

        if ($category !== null) {
            $query->inCategory($category);
        }

        return view('site.announcements', [
            'announcements' => $query->paginate(10)->withQueryString(),
            'category' => $category,
        ]);
    }

    public function sitemap(): Response
    {
        return response()->view('site.sitemap', [
            'pages' => Page::published()->get(),
            'issues' => Issue::published()->get(),
            'articles' => Article::published()->get(),
        ])->header('Content-Type', 'application/xml');
    }
}
