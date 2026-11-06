<?php

namespace App\Models;

use App\Support\SafeMarkdown;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;

#[Fillable(['type', 'sort_order', 'is_visible', 'starts_at', 'ends_at', 'heading', 'subtitle', 'body', 'button_label', 'button_url', 'desktop_media_id', 'mobile_media_id', 'cover_media_id'])]
class HomeBlock extends Model
{
    public const TYPE_HERO = 'hero';

    public const TYPE_PROCEEDINGS = 'proceedings';

    public const TYPE_ANNOUNCEMENTS = 'announcements';

    public const TYPE_TEXT = 'text';

    /** A two-column table; each line of the body is "Label | Value". */
    public const TYPE_TABLE = 'table';

    public const TYPES = [self::TYPE_HERO, self::TYPE_PROCEEDINGS, self::TYPE_ANNOUNCEMENTS, self::TYPE_TEXT, self::TYPE_TABLE];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Blocks that are switched on and inside their schedule right now, in display order.
     */
    public function scopeLive(Builder $query): void
    {
        $query->where('is_visible', true)
            ->where(fn (Builder $schedule) => $schedule->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $schedule) => $schedule->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->with(['desktopMedia', 'mobileMedia'])
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function desktopMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'desktop_media_id');
    }

    public function mobileMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'mobile_media_id');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function bodyHtml(): HtmlString
    {
        return SafeMarkdown::render($this->body);
    }
}
