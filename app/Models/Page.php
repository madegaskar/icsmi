<?php

namespace App\Models;

use App\Support\SafeMarkdown;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;

#[Fillable(['slug', 'title', 'body', 'published_at'])]
class Page extends Model
{
    use SoftDeletes;

    /** The pages the site started with. Seeders use this list; routing does not. */
    public const SLUGS = [
        'about',
        'scope',
        'call-for-papers',
        'important-dates',
        'registration',
        'program',
        'author-guidelines',
        'ethics',
        'contact',
    ];

    public const STATE_DRAFT = 'draft';

    public const STATE_SCHEDULED = 'scheduled';

    public const STATE_PUBLISHED = 'published';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Pages that visitors can see right now.
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function state(): string
    {
        return match (true) {
            $this->published_at === null => self::STATE_DRAFT,
            $this->published_at->isFuture() => self::STATE_SCHEDULED,
            default => self::STATE_PUBLISHED,
        };
    }

    public function isPublished(): bool
    {
        return $this->state() === self::STATE_PUBLISHED;
    }

    public function revisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PageRevision::class)->orderByDesc('id');
    }

    /**
     * Page bodies are Markdown: raw HTML is stripped and unsafe link schemes are dropped.
     */
    public function bodyHtml(): HtmlString
    {
        return SafeMarkdown::render($this->body);
    }
}
