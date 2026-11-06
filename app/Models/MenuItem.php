<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['location', 'parent_id', 'label', 'link_type', 'url', 'media_id', 'sort_order', 'is_visible'])]
class MenuItem extends Model
{
    public const LOCATION_HEADER = 'header';

    public const LOCATION_FOOTER = 'footer';

    public const LOCATIONS = [self::LOCATION_HEADER, self::LOCATION_FOOTER];

    public const TYPE_INTERNAL = 'internal';

    public const TYPE_EXTERNAL = 'external';

    public const TYPE_MEDIA = 'media';

    /** A heading with no link of its own, used to group footer links. */
    public const TYPE_GROUP = 'group';

    public const TYPES = [self::TYPE_INTERNAL, self::TYPE_EXTERNAL, self::TYPE_MEDIA, self::TYPE_GROUP];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * The URL this item links to, or null when it has nothing to link to.
     */
    public function href(): ?string
    {
        return match ($this->link_type) {
            self::TYPE_MEDIA => $this->media
                ? route('media.show', [$this->media, Media::VARIANT_ORIGINAL])
                : null,
            self::TYPE_INTERNAL, self::TYPE_EXTERNAL => $this->url,
            default => null,
        };
    }

    /**
     * Visible top-level items for one location, each with its visible children.
     * Items that would lead nowhere are left out, unless they group visible children.
     *
     * @return Collection<int, self>
     */
    public static function publicTree(string $location): Collection
    {
        return self::query()
            ->with('media')
            ->where('location', $location)
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (self $item) => $item->setRelation('children', $item->visibleChildren()))
            ->filter(fn (self $item) => $item->href() !== null || ($item->getRelation("children")->isNotEmpty()))
            ->values();
    }

    /**
     * @return Collection<int, self>
     */
    private function visibleChildren(): Collection
    {
        return self::query()
            ->with('media')
            ->where('parent_id', $this->id)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (self $child) => $child->href() !== null)
            ->values();
    }
}
