<?php

use App\Enums\Role;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\BoardMemberController;
use App\Http\Controllers\Admin\HomeBlockController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\SidebarSectionController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Editorial\ArticleController as EditorialArticleController;
use App\Http\Controllers\Editorial\IssueController;
use App\Http\Controllers\Editorial\MediaLibraryController;
use App\Http\Controllers\Editorial\PublicSubmissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Site\ArticleController;
use App\Http\Controllers\Site\MediaController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ProceedingController;
use App\Http\Controllers\Site\SubmissionController;
use App\Http\Controllers\Workflow\AuthorManuscriptController;
use App\Http\Controllers\Workflow\EditorController;
use App\Http\Controllers\Workflow\ManuscriptFileController;
use App\Http\Controllers\Workflow\ReviewerController;
use App\Models\Media;
use App\Services\ReportBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', function () {
    $rules = config('site.noindex') ? "User-agent: *\nDisallow: /\n" : "User-agent: *\nDisallow:\n";

    return response($rules, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/editorial-board', [PageController::class, 'editorialBoard'])->name('editorial-board');
Route::get('/speakers', [PageController::class, 'speakers'])->name('speakers');
Route::get('/announcements', [PageController::class, 'announcements'])->name('announcements');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

Route::get('/submission', [SubmissionController::class, 'create'])->name('submission.create');
Route::post('/submission', [SubmissionController::class, 'store'])->middleware('throttle:public-submission')->name('submission.store');
Route::get('/submission/confirm/{submission}', [SubmissionController::class, 'confirmForm'])->middleware('signed')->name('submission.confirm');
Route::post('/submission/confirm/{submission}', [SubmissionController::class, 'confirm'])->middleware(['signed', 'throttle:10,1']);

Route::get('/current', [ProceedingController::class, 'current'])->name('proceedings.current');
Route::get('/archives', [ProceedingController::class, 'archives'])->name('proceedings.archives');
Route::get('/archives/{slug}', [ProceedingController::class, 'show'])->name('proceedings.show');

Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/articles/{slug}/pdf', [ArticleController::class, 'pdf'])->middleware('throttle:60,1')->name('articles.pdf');
Route::get('/search', [ArticleController::class, 'search'])->middleware('throttle:60,1')->name('search');

Route::get('/media/{media}/{variant}', [MediaController::class, 'show'])
    ->whereIn('variant', ['desktop', 'mobile', Media::VARIANT_ORIGINAL])
    ->name('media.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::resource('board', BoardMemberController::class)->except('show');
        Route::resource('menu', MenuItemController::class)->except('show')->parameters(['menu' => 'menuItem']);
        Route::get('settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SiteSettingController::class, 'update'])->name('settings.update');
        Route::get('seo', [SeoSettingController::class, 'edit'])->name('seo.edit');
        Route::put('seo', [SeoSettingController::class, 'update'])->name('seo.update');
        Route::resource('home-blocks', HomeBlockController::class)->except('show')->parameters(['home-blocks' => 'homeBlock']);
        Route::resource('sidebar', SidebarSectionController::class)->except('show')->parameters(['sidebar' => 'sidebarSection']);
        Route::get('/audit-log', AuditLogController::class)->name('audit-log');
    });

    Route::middleware('role:admin,editor')->prefix('admin')->name('admin.')->controller(AdminPageController::class)->group(function () {
        Route::get('/pages', 'index')->name('pages.index');
        Route::get('/pages/create', 'create')->name('pages.create');
        Route::post('/pages', 'store')->name('pages.store');
        Route::get('/pages/{page}/edit', 'edit')->name('pages.edit');
        Route::put('/pages/{page}', 'update')->name('pages.update');
        Route::delete('/pages/{page}', 'destroy')->name('pages.destroy');
        Route::get('/pages/{page}/preview', 'preview')->name('pages.preview');
        Route::get('/pages/{page}/revisions', 'revisions')->name('pages.revisions');
        Route::post('/pages/{page}/revisions/{revision}/restore', 'restore')->name('pages.revisions.restore');
    });

    Route::middleware('role:admin,editor')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('announcements', AnnouncementController::class)->except('show');
    });

    Route::middleware('role:admin')->prefix('admin/content')->name('admin.content.')->controller(ContentController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/export', 'export')->name('export');
        Route::post('/import', 'store')->name('import');
        Route::get('/import/{token}', 'preview')->where('token', '[A-Za-z0-9]{40}')->name('preview');
        Route::post('/import/{token}', 'apply')->where('token', '[A-Za-z0-9]{40}')->name('apply');
    });

    Route::middleware('role:admin,editor')->controller(ReportController::class)->group(function () {
        Route::get('/reports', 'index')->name('reports.index');
        Route::get('/reports/export/{section}.{format}', 'export')
            ->whereIn('section', ReportBuilder::SECTIONS)
            ->whereIn('format', ['csv', 'xlsx'])
            ->name('reports.export');
    });

    Route::middleware('role:admin,editor')->prefix('library')->name('library.')->controller(MediaLibraryController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->middleware('throttle:20,1')->name('store');
        Route::get('/{media}/edit', 'edit')->name('edit');
        Route::put('/{media}', 'update')->name('update');
        Route::delete('/{media}', 'destroy')->name('destroy');
    });

    Route::get('/manuscript-files/{file}', ManuscriptFileController::class)->name('manuscript-files.download');

    Route::middleware('role:author')->prefix('author')->name('author.')->controller(AuthorManuscriptController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard');
        Route::get('/manuscripts/new', 'create')->name('manuscripts.create');
        Route::post('/manuscripts', 'store')->middleware('throttle:10,1')->name('manuscripts.store');
        Route::get('/manuscripts/{manuscript}', 'show')->name('manuscripts.show');
        Route::post('/manuscripts/{manuscript}/revision', 'revise')->middleware('throttle:10,1')->name('manuscripts.revise');
    });

    Route::middleware('role:reviewer')->prefix('reviewer')->name('reviewer.')->controller(ReviewerController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard');
        Route::get('/assignments/{assignment}', 'show')->name('assignments.show');
        Route::post('/assignments/{assignment}/respond', 'respond')->name('assignments.respond');
        Route::post('/assignments/{assignment}/review', 'submit')->name('assignments.submit');
    });

    Route::middleware('role:editor')->prefix('editor')->name('editor.')->controller(EditorController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard');
        Route::get('/manuscripts/{manuscript}', 'show')->name('manuscripts.show');
        Route::post('/manuscripts/{manuscript}/status', 'updateStatus')->name('manuscripts.status');
        Route::post('/manuscripts/{manuscript}/reviewers', 'assignReviewer')->name('manuscripts.reviewers');
        Route::post('/manuscripts/{manuscript}/publish', 'publish')->middleware('throttle:10,1')->name('manuscripts.publish');
        Route::post('/assignments/{assignment}/cancel', 'cancelAssignment')->name('assignments.cancel');
    });

    Route::middleware('role:editor')->prefix('editor')->name('editor.')->group(function () {
        Route::resource('issues', IssueController::class)->except(['show', 'destroy']);
        Route::get('/submissions', [PublicSubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/{submission}', [PublicSubmissionController::class, 'show'])->name('submissions.show');
        Route::get('/submissions/{submission}/file', [PublicSubmissionController::class, 'file'])->name('submissions.file');
        Route::resource('articles', EditorialArticleController::class)->except(['show', 'destroy']);
    });
});

require __DIR__.'/auth.php';

Route::get('/{page:slug}', [PageController::class, 'show'])
    ->where('page', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('page');
