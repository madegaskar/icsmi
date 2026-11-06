<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\HomeBlock;
use App\Models\Media;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Plans and applies a content import. Planning changes nothing, so the admin can review it first.
 * Pages are matched by address, announcements by title and text, and the menu and home page are replaced.
 */
final class ContentImporter
{
    public function __construct(private readonly PageEditor $pages) {}

    /**
     * @param  array<string, mixed>  $payload  A file that has already passed ContentImportValidator.
     * @return array{pages: array<int, array<string, string>>, announcements: array<int, array<string, string>>, menu_items: int, home_blocks: int, errors: array<int, string>}
     */
    public function plan(array $payload): array
    {
        $pages = collect($payload['pages'] ?? [])->map(fn (array $item) => [
            'slug' => $item['slug'],
            'title' => $item['title'],
            'action' => $this->pageAction($item),
        ])->values();

        $announcements = collect($payload['announcements'] ?? [])->map(fn (array $item) => [
            'title' => $item['title'],
            'action' => $this->announcementExists($item) ? 'unchanged' : 'create',
        ])->values();

        return [
            'pages' => $pages->all(),
            'announcements' => $announcements->all(),
            'menu_items' => count($payload['menu_items'] ?? []),
            'home_blocks' => count($payload['home_blocks'] ?? []),
            'errors' => [...$this->blockedPages($pages->all()), ...$this->missingMedia($payload)],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function apply(array $payload, User $admin): void
    {
        $plan = $this->plan($payload);

        if ($plan['errors'] !== []) {
            throw ValidationException::withMessages(['content' => $plan['errors']]);
        }

        DB::transaction(function () use ($payload, $admin) {
            $this->applyPages($payload['pages'] ?? [], $admin);
            $this->applyAnnouncements($payload['announcements'] ?? []);

            $mediaIds = Media::pluck('id', 'uuid')->all();
            $this->replaceMenu($payload['menu_items'] ?? [], $mediaIds);
            $this->replaceHomeBlocks($payload['home_blocks'] ?? [], $mediaIds);

            AuditLog::record('content.imported', $admin->id, [
                'pages' => count($payload['pages'] ?? []),
                'announcements' => count($payload['announcements'] ?? []),
                'menu_items' => count($payload['menu_items'] ?? []),
                'home_blocks' => count($payload['home_blocks'] ?? []),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function pageAction(array $item): string
    {
        $existing = Page::withTrashed()->where('slug', $item['slug'])->first();

        if ($existing === null) {
            return 'create';
        }

        if ($existing->trashed()) {
            return 'blocked';
        }

        return $existing->title === $item['title']
            && $existing->body === $item['body']
            && $this->sameTime($existing->published_at, $item['published_at'] ?? null)
            ? 'unchanged'
            : 'update';
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function announcementExists(array $item): bool
    {
        return Announcement::where('title', $item['title'])->where('body', $item['body'])->exists();
    }

    /**
     * @param  array<int, array<string, string>>  $pages
     * @return array<int, string>
     */
    private function blockedPages(array $pages): array
    {
        return collect($pages)
            ->where('action', 'blocked')
            ->map(fn (array $page) => "pages: the address /{$page['slug']} belongs to a deleted page. Choose another address in the file.")
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private function missingMedia(array $payload): array
    {
        $referenced = collect($payload['menu_items'] ?? [])->pluck('media_uuid')
            ->merge(collect($payload['home_blocks'] ?? [])->pluck('desktop_media_uuid'))
            ->merge(collect($payload['home_blocks'] ?? [])->pluck('mobile_media_uuid'))
            ->filter()
            ->unique();

        $present = Media::whereIn('uuid', $referenced)->pluck('uuid');

        return $referenced->diff($present)
            ->map(fn (string $uuid) => "A file ({$uuid}) is missing from the Media Library. Upload it first, or remove the reference.")
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function applyPages(array $items, User $admin): void
    {
        foreach ($items as $item) {
            $data = [
                'title' => $item['title'],
                'slug' => $item['slug'],
                'body' => $item['body'],
                'visibility' => blank($item['published_at'] ?? null) ? PageEditor::VISIBILITY_DRAFT : PageEditor::VISIBILITY_PUBLISH,
                'publish_at' => $item['published_at'] ?? null,
            ];

            $existing = Page::where('slug', $item['slug'])->first();

            match ($this->pageAction($item)) {
                'create' => $this->pages->create($data, $admin),
                'update' => $this->pages->update($existing, $data, $admin),
                default => null,
            };
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function applyAnnouncements(array $items): void
    {
        foreach ($items as $item) {
            if ($this->announcementExists($item)) {
                continue;
            }

            Announcement::create([
                'title' => $item['title'],
                'body' => $item['body'],
                'category' => $item['category'],
                'published_at' => $this->time($item['published_at'] ?? null),
                'ends_at' => $this->time($item['ends_at'] ?? null),
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, int>  $mediaIds
     */
    private function replaceMenu(array $items, array $mediaIds): void
    {
        MenuItem::query()->delete();

        $newIds = [];

        foreach (collect($items)->whereNull('parent_id') as $item) {
            $newIds[$item['id']] = MenuItem::create($this->menuAttributes($item, null, $mediaIds))->id;
        }

        foreach (collect($items)->whereNotNull('parent_id') as $item) {
            MenuItem::create($this->menuAttributes($item, $newIds[$item['parent_id']], $mediaIds));
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, int>  $mediaIds
     * @return array<string, mixed>
     */
    private function menuAttributes(array $item, ?int $parentId, array $mediaIds): array
    {
        return [
            'location' => $item['location'],
            'parent_id' => $parentId,
            'label' => $item['label'],
            'link_type' => $item['link_type'],
            'url' => in_array($item['link_type'], [MenuItem::TYPE_INTERNAL, MenuItem::TYPE_EXTERNAL], true) ? $item['url'] : null,
            'media_id' => $mediaIds[$item['media_uuid'] ?? ''] ?? null,
            'sort_order' => $item['sort_order'],
            'is_visible' => (bool) $item['is_visible'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, int>  $mediaIds
     */
    private function replaceHomeBlocks(array $items, array $mediaIds): void
    {
        HomeBlock::query()->delete();

        foreach ($items as $item) {
            HomeBlock::create([
                'type' => $item['type'],
                'sort_order' => $item['sort_order'],
                'is_visible' => (bool) $item['is_visible'],
                'starts_at' => $this->time($item['starts_at'] ?? null),
                'ends_at' => $this->time($item['ends_at'] ?? null),
                'heading' => $item['heading'] ?? null,
                'subtitle' => $item['subtitle'] ?? null,
                'body' => $item['body'] ?? null,
                'button_label' => $item['button_label'] ?? null,
                'button_url' => $item['button_url'] ?? null,
                'desktop_media_id' => $mediaIds[$item['desktop_media_uuid'] ?? ''] ?? null,
                'mobile_media_id' => $mediaIds[$item['mobile_media_uuid'] ?? ''] ?? null,
            ]);
        }
    }

    private function time(?string $value): ?Carbon
    {
        return blank($value) ? null : Carbon::parse($value);
    }

    private function sameTime(?Carbon $existing, ?string $incoming): bool
    {
        return ($existing?->getTimestamp()) === ($this->time($incoming)?->getTimestamp());
    }
}
