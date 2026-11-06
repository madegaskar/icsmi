<?php

namespace App\Models;

use App\Rules\SafeLink;
use App\Support\SafeMarkdown;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

#[Fillable(['title', 'kind', 'body', 'sort_order', 'is_visible'])]
class SidebarSection extends Model
{
    public const KIND_LINKS = 'links';

    public const KIND_TEXT = 'text';

    /** The live visitor statistics. Its body is ignored. */
    public const KIND_VISITOR = 'visitor';

    public const KINDS = [self::KIND_LINKS, self::KIND_TEXT, self::KIND_VISITOR];

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

    public function scopeLive(Builder $query): void
    {
        $query->where('is_visible', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * One "Label | address" per line. Lines with a missing label or an unsafe address are skipped.
     *
     * @return Collection<int, array{label: string, url: string}>
     */
    public function links(): Collection
    {
        return collect(preg_split('/\R/', (string) $this->body))
            ->map(fn (string $line) => array_map('trim', explode('|', $line, 2)) + [1 => ''])
            ->filter(fn (array $parts) => $parts[0] !== '' && $parts[1] !== '' && SafeLink::accepts($parts[1]))
            ->map(fn (array $parts) => ['label' => $parts[0], 'url' => $parts[1]])
            ->values();
    }

    public function bodyHtml(): HtmlString
    {
        return SafeMarkdown::render($this->body);
    }
}
