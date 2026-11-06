<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * A saved copy of a page. Revisions are a history, so they can be added but never changed or removed.
 */
#[Fillable(['page_id', 'user_id', 'title', 'slug', 'body', 'published_at'])]
class PageRevision extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Page revisions are a permanent history and cannot be changed.'));
        static::deleting(fn () => throw new LogicException('Page revisions are a permanent history and cannot be deleted.'));
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
