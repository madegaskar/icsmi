<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'body', 'category', 'published_at', 'ends_at'])]
class Announcement extends Model
{
    public const CATEGORY_ANNOUNCEMENT = 'announcement';

    public const CATEGORY_NEWS = 'news';

    public const CATEGORY_DEADLINE = 'deadline';

    public const CATEGORIES = [self::CATEGORY_ANNOUNCEMENT, self::CATEGORY_NEWS, self::CATEGORY_DEADLINE];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Published and not yet past its end date.
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn (Builder $ends) => $ends->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeInCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }

    public function categoryLabel(): string
    {
        return self::labelFor($this->category);
    }

    public static function labelFor(string $category): string
    {
        return match ($category) {
            self::CATEGORY_ANNOUNCEMENT => 'Pengumuman',
            self::CATEGORY_DEADLINE => 'Tenggat',
            default => 'Berita',
        };
    }
}
