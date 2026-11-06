<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\HomeBlock;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Support\Carbon;

/**
 * Builds the content export. Accounts, password hashes, revisions, and audit entries are never included.
 */
final class ContentExporter
{
    public const FORMAT = 'icsmi-content';

    public const VERSION = 1;

    /**
     * @return array<string, mixed>
     */
    public function export(): array
    {
        return [
            'format' => self::FORMAT,
            'version' => self::VERSION,
            'exported_at' => now()->toIso8601String(),
            'pages' => Page::orderBy('slug')->get()->map(fn (Page $page) => [
                'slug' => $page->slug,
                'title' => $page->title,
                'body' => $page->body,
                'published_at' => $this->iso($page->published_at),
            ])->all(),
            'announcements' => Announcement::orderBy('id')->get()->map(fn (Announcement $item) => [
                'title' => $item->title,
                'body' => $item->body,
                'category' => $item->category,
                'published_at' => $this->iso($item->published_at),
                'ends_at' => $this->iso($item->ends_at),
            ])->all(),
            'media' => Media::orderBy('id')->get()->map(fn (Media $media) => [
                'uuid' => $media->uuid,
                'original_name' => $media->original_name,
                'kind' => $media->kind,
                'mime_type' => $media->mime_type,
                'alt_text' => $media->alt_text,
                'caption' => $media->caption,
                'width' => $media->width,
                'height' => $media->height,
                'size' => $media->size,
            ])->all(),
            'menu_items' => MenuItem::with('media')->orderBy('id')->get()->map(fn (MenuItem $item) => [
                'id' => $item->id,
                'parent_id' => $item->parent_id,
                'location' => $item->location,
                'label' => $item->label,
                'link_type' => $item->link_type,
                'url' => $item->url,
                'media_uuid' => $item->media?->uuid,
                'sort_order' => $item->sort_order,
                'is_visible' => $item->is_visible,
            ])->all(),
            'home_blocks' => HomeBlock::with(['desktopMedia', 'mobileMedia'])->orderBy('id')->get()->map(fn (HomeBlock $block) => [
                'type' => $block->type,
                'sort_order' => $block->sort_order,
                'is_visible' => $block->is_visible,
                'starts_at' => $this->iso($block->starts_at),
                'ends_at' => $this->iso($block->ends_at),
                'heading' => $block->heading,
                'subtitle' => $block->subtitle,
                'body' => $block->body,
                'button_label' => $block->button_label,
                'button_url' => $block->button_url,
                'desktop_media_uuid' => $block->desktopMedia?->uuid,
                'mobile_media_uuid' => $block->mobileMedia?->uuid,
            ])->all(),
        ];
    }

    private function iso(?Carbon $time): ?string
    {
        return $time?->toIso8601String();
    }
}
