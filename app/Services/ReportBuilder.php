<?php

namespace App\Services;

use App\Enums\ManuscriptStatus;
use App\Enums\ReviewStatus;
use App\Models\Article;
use App\Models\ArticleStat;
use App\Models\Issue;
use App\Models\Manuscript;
use App\Models\ReviewAssignment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;

class ReportBuilder
{
    public const SECTIONS = ['manuscripts', 'monthly', 'statuses', 'issues', 'articles', 'reviewers'];

    private ?Carbon $from;

    private ?Carbon $to;

    public function __construct(?string $from = null, ?string $to = null)
    {
        $this->from = $from ? Carbon::parse($from)->startOfDay() : null;
        $this->to = $to ? Carbon::parse($to)->endOfDay() : null;
    }

    /**
     * @return array<string, int|float|null>
     */
    public function summary(): array
    {
        $manuscripts = $this->manuscripts();
        $accepted = $manuscripts->whereIn('status', [ManuscriptStatus::Accepted, ManuscriptStatus::Published])->count();
        $rejected = $manuscripts->where('status', ManuscriptStatus::Rejected)->count();
        $decided = $accepted + $rejected;

        return [
            'total' => $manuscripts->count(),
            'accepted' => $accepted,
            'rejected' => $rejected,
            'acceptance_rate' => $decided ? round($accepted / $decided * 100, 1) : null,
            'rejection_rate' => $decided ? round($rejected / $decided * 100, 1) : null,
            'days_to_decision' => $this->averageDays($manuscripts->whereNotNull('decided_at'), fn (Manuscript $manuscript) => $manuscript->decided_at),
            'days_to_publication' => $this->averageDays($manuscripts->filter(fn (Manuscript $manuscript) => $manuscript->article?->published_at), fn (Manuscript $manuscript) => $manuscript->article->published_at),
            'views' => (int) $this->stats()->sum('views'),
            'downloads' => (int) $this->stats()->sum('downloads'),
        ];
    }

    /**
     * @return array{headers: list<string>, rows: list<list<mixed>>}
     */
    public function section(string $section): array
    {
        return match ($section) {
            'manuscripts' => $this->manuscriptRows(),
            'monthly' => $this->monthlyRows(),
            'statuses' => $this->statusRows(),
            'issues' => $this->issueRows(),
            'articles' => $this->articleRows(),
            'reviewers' => $this->reviewerRows(),
        };
    }

    /**
     * @return array{headers: list<string>, rows: list<list<mixed>>}
     */
    private function manuscriptRows(): array
    {
        return [
            'headers' => ['Code', 'Title', 'Status', 'Version', 'Submitted', 'Decided', 'Published'],
            'rows' => $this->manuscripts()->map(fn (Manuscript $manuscript) => [
                $manuscript->code,
                $manuscript->title,
                $manuscript->status->label(),
                $manuscript->current_version,
                $manuscript->submitted_at->toDateString(),
                $manuscript->decided_at?->toDateString(),
                $manuscript->article?->published_at?->toDateString(),
            ])->values()->all(),
        ];
    }

    /**
     * @return array{headers: list<string>, rows: list<list<mixed>>}
     */
    private function monthlyRows(): array
    {
        return [
            'headers' => ['Month', 'Manuscripts'],
            'rows' => $this->manuscripts()
                ->groupBy(fn (Manuscript $manuscript) => $manuscript->submitted_at->format('Y-m'))
                ->sortKeys()
                ->map(fn (Collection $group, string $month) => [$month, $group->count()])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{headers: list<string>, rows: list<list<mixed>>}
     */
    private function statusRows(): array
    {
        $manuscripts = $this->manuscripts();

        return [
            'headers' => ['Status', 'Manuscripts'],
            'rows' => collect(ManuscriptStatus::cases())
                ->map(fn (ManuscriptStatus $status) => [$status->label(), $manuscripts->where('status', $status)->count()])
                ->all(),
        ];
    }

    /**
     * @return array{headers: list<string>, rows: list<list<mixed>>}
     */
    private function issueRows(): array
    {
        $totals = $this->articleTotals();
        $articles = Article::get(['id', 'issue_id', 'manuscript_id'])->groupBy('issue_id');

        return [
            'headers' => ['Proceedings', 'Year', 'Articles', 'From submissions', 'Views', 'Downloads'],
            'rows' => Issue::orderByDesc('year')->orderByDesc('id')->get()->map(function (Issue $issue) use ($totals, $articles) {
                $group = $articles->get($issue->id, collect());

                return [
                    $issue->title,
                    $issue->year,
                    $group->count(),
                    $group->whereNotNull('manuscript_id')->count(),
                    $group->sum(fn (Article $article) => $totals->get($article->id)?->views ?? 0),
                    $group->sum(fn (Article $article) => $totals->get($article->id)?->downloads ?? 0),
                ];
            })->all(),
        ];
    }

    /**
     * @return array{headers: list<string>, rows: list<list<mixed>>}
     */
    private function articleRows(): array
    {
        $totals = $this->articleTotals();

        return [
            'headers' => ['Article', 'Proceedings', 'Published', 'Views', 'Downloads'],
            'rows' => Article::with('issue:id,title')->latest('id')->get()->map(fn (Article $article) => [
                $article->title,
                $article->issue->title,
                $article->published_at?->toDateString(),
                (int) ($totals->get($article->id)?->views ?? 0),
                (int) ($totals->get($article->id)?->downloads ?? 0),
            ])->all(),
        ];
    }

    /**
     * @return array{headers: list<string>, rows: list<list<mixed>>}
     */
    private function reviewerRows(): array
    {
        return [
            'headers' => ['Reviewer', 'Email', 'Invited', 'Accepted', 'Declined', 'Completed', 'Average days to complete'],
            'rows' => ReviewAssignment::with('reviewer:id,name,email')
                ->tap(fn (Builder $query) => $this->inRange($query, 'created_at'))
                ->where('status', '!=', ReviewStatus::Cancelled)
                ->get()
                ->groupBy('reviewer_id')
                ->map(function (Collection $group) {
                    $completed = $group->where('status', ReviewStatus::Completed);

                    return [
                        $group->first()->reviewer->name,
                        $group->first()->reviewer->email,
                        $group->count(),
                        $group->whereNotNull('responded_at')->where('status', '!=', ReviewStatus::Declined)->count(),
                        $group->where('status', ReviewStatus::Declined)->count(),
                        $completed->count(),
                        $completed->isEmpty() ? null : round($completed->avg(fn (ReviewAssignment $assignment) => $assignment->created_at->diffInDays($assignment->completed_at)), 1),
                    ];
                })
                ->sortBy(fn (array $row) => -$row[2])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return Collection<int, Manuscript>
     */
    private function manuscripts(): Collection
    {
        return Manuscript::with('article:id,manuscript_id,published_at')
            ->tap(fn (Builder $query) => $this->inRange($query, 'submitted_at'))
            ->latest('submitted_at')
            ->get();
    }

    private function stats(): Builder
    {
        return ArticleStat::query()
            ->when($this->from, fn (Builder $query) => $query->where('date', '>=', $this->from->toDateString()))
            ->when($this->to, fn (Builder $query) => $query->where('date', '<=', $this->to->toDateString()));
    }

    /**
     * @return SupportCollection<int, ArticleStat>
     */
    private function articleTotals(): SupportCollection
    {
        return $this->stats()
            ->selectRaw('article_id, SUM(views) AS views, SUM(downloads) AS downloads')
            ->groupBy('article_id')
            ->get()
            ->keyBy('article_id');
    }

    private function inRange(Builder $query, string $column): void
    {
        $query->when($this->from, fn (Builder $query) => $query->where($column, '>=', $this->from))
            ->when($this->to, fn (Builder $query) => $query->where($column, '<=', $this->to));
    }

    /**
     * @param  Collection<int, Manuscript>  $manuscripts
     * @param  callable(Manuscript): CarbonInterface  $end
     */
    private function averageDays(Collection $manuscripts, callable $end): ?float
    {
        return $manuscripts->isEmpty()
            ? null
            : round($manuscripts->avg(fn (Manuscript $manuscript) => $manuscript->submitted_at->diffInDays($end($manuscript))), 1);
    }
}
