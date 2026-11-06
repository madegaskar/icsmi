<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\HomeBlock;
use App\Models\MenuItem;
use App\Rules\NoControlCharacters;
use App\Rules\PageSlug;
use App\Rules\SafeLink;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Checks an uploaded content file before anything is previewed or saved.
 * Text must be plain or Markdown. Any HTML tag is rejected, so nothing raw can enter the database.
 */
final class ContentImportValidator
{
    private const MAX_ITEMS_PER_SECTION = 500;

    private const HTML_TAG = '/<\/?[A-Za-z!][^>]*>/';

    private const MAX_SORT_ORDER = 9999;

    /**
     * @return array<int, string>  Human-readable problems. Empty when the file can be imported.
     */
    public function validate(mixed $payload): array
    {
        if (! is_array($payload)) {
            return ['The file must contain a JSON object.'];
        }

        $errors = [];

        if (($payload['format'] ?? null) !== ContentExporter::FORMAT) {
            $errors[] = 'This is not an ICSMI content file.';
        }

        if (($payload['version'] ?? null) !== ContentExporter::VERSION) {
            $errors[] = 'This file version is not supported.';
        }

        foreach ($this->rules() as $section => $rules) {
            $items = $payload[$section] ?? [];

            if (! is_array($items) || count($items) > self::MAX_ITEMS_PER_SECTION) {
                $errors[] = "{$section} must be a list of at most ".self::MAX_ITEMS_PER_SECTION.' items.';

                continue;
            }

            foreach ($items as $index => $item) {
                $label = $section.' #'.($index + 1);

                foreach (Validator::make(is_array($item) ? $item : [], $rules)->errors()->all() as $message) {
                    $errors[] = "{$label}: {$message}";
                }

                foreach ($this->htmlTagsIn($item) as $field) {
                    $errors[] = "{$label}: {$field} contains HTML tags. Use plain text or Markdown.";
                }
            }
        }

        return [...$errors, ...$this->structureErrors($payload)];
    }

    /**
     * @return array<string, array<string, array<int, mixed>>>
     */
    private function rules(): array
    {
        return [
            'pages' => [
                'slug' => ['required', new PageSlug],
                'title' => ['required', 'string', 'max:255'],
                'body' => ['required', 'string', 'max:50000'],
                'published_at' => ['nullable', 'date'],
            ],
            'announcements' => [
                'title' => ['required', 'string', 'min:5', 'max:150', new NoControlCharacters],
                'body' => ['required', 'string', 'min:10', 'max:10000', new NoControlCharacters],
                'category' => ['required', Rule::in(Announcement::CATEGORIES)],
                'published_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date'],
            ],
            'menu_items' => [
                'id' => ['required', 'integer'],
                'parent_id' => ['nullable', 'integer'],
                'location' => ['required', Rule::in(MenuItem::LOCATIONS)],
                'label' => ['required', 'string', 'max:100'],
                'link_type' => ['required', Rule::in(MenuItem::TYPES)],
                'url' => ['nullable', 'string', 'max:255', new SafeLink],
                'media_uuid' => ['nullable', 'uuid'],
                'sort_order' => ['required', 'integer', 'min:0', 'max:'.self::MAX_SORT_ORDER],
                'is_visible' => ['required', 'boolean'],
            ],
            'home_blocks' => [
                'type' => ['required', Rule::in(HomeBlock::TYPES)],
                'sort_order' => ['required', 'integer', 'min:0', 'max:'.self::MAX_SORT_ORDER],
                'is_visible' => ['required', 'boolean'],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date'],
                'heading' => ['nullable', 'string', 'max:150'],
                'subtitle' => ['nullable', 'string', 'max:255'],
                'body' => ['nullable', 'string', 'max:10000'],
                'button_label' => ['nullable', 'string', 'max:60'],
                'button_url' => ['nullable', 'string', 'max:255', new SafeLink],
                'desktop_media_uuid' => ['nullable', 'uuid'],
                'mobile_media_uuid' => ['nullable', 'uuid'],
            ],
        ];
    }

    /**
     * Rules that look across items: menu parents, required links, and a single hero.
     *
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private function structureErrors(array $payload): array
    {
        $errors = [];
        $menu = is_array($payload['menu_items'] ?? null) ? $payload['menu_items'] : [];
        $ids = collect($menu)->pluck('id')->filter(fn ($id) => is_int($id));

        if ($ids->count() !== $ids->unique()->count()) {
            $errors[] = 'menu_items: each id must be used only once.';
        }

        foreach ($menu as $item) {
            if (! is_array($item)) {
                continue;
            }

            if (in_array($item['link_type'] ?? null, [MenuItem::TYPE_INTERNAL, MenuItem::TYPE_EXTERNAL], true) && blank($item['url'] ?? null)) {
                $errors[] = 'menu_items: link "'.($item['label'] ?? '?').'" needs an address.';
            }

            if (! blank($item['parent_id'] ?? null)) {
                $parent = collect($menu)->firstWhere('id', $item['parent_id']);

                if ($parent === null || ! blank($parent['parent_id'] ?? null)) {
                    $errors[] = 'menu_items: "'.($item['label'] ?? '?').'" must belong to a top-level item in the same file.';
                }
            }
        }

        $heroes = collect($payload['home_blocks'] ?? [])->where('type', HomeBlock::TYPE_HERO)->count();

        if ($heroes > 1) {
            $errors[] = 'home_blocks: only one hero block is allowed.';
        }

        return $errors;
    }

    /**
     * @return array<int, string>  Names of text fields that contain HTML.
     */
    private function htmlTagsIn(mixed $item, string $path = ''): array
    {
        if (is_string($item)) {
            return preg_match(self::HTML_TAG, $item) ? [$path !== '' ? $path : 'text'] : [];
        }

        if (! is_array($item)) {
            return [];
        }

        return collect($item)
            ->flatMap(fn ($value, $key) => $this->htmlTagsIn($value, (string) $key))
            ->values()
            ->all();
    }
}
