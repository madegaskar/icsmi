<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['manuscript_id', 'issue_id', 'slug', 'title', 'abstract', 'reference_list', 'doi', 'first_page', 'last_page', 'pdf_path', 'published_at'])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function authors(): HasMany
    {
        return $this->hasMany(ArticleAuthor::class)->orderBy('position');
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class);
    }

    public function stats(): HasMany
    {
        return $this->hasMany(ArticleStat::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('issue', fn (Builder $issue) => $issue->published());
    }

    public function pages(): ?string
    {
        if ($this->first_page === null) {
            return null;
        }

        return $this->last_page === null || $this->last_page === $this->first_page
            ? (string) $this->first_page
            : $this->first_page.'–'.$this->last_page;
    }
}
