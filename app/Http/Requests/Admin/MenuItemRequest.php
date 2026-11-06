<?php

namespace App\Http\Requests\Admin;

use App\Models\MenuItem;
use App\Models\Media;
use App\Rules\SafeLink;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    private const MAX_SORT_ORDER = 9999;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'location' => ['required', Rule::in(MenuItem::LOCATIONS)],
            'label' => ['required', 'string', 'max:100'],
            'link_type' => ['required', Rule::in(MenuItem::TYPES)],
            'url' => ['nullable', 'string', 'max:255', new SafeLink],
            'media_id' => ['nullable', 'integer', Rule::exists(Media::class, 'id')],
            'parent_id' => ['nullable', 'integer', Rule::exists(MenuItem::class, 'id')],
            'sort_order' => ['required', 'integer', 'min:0', 'max:'.self::MAX_SORT_ORDER],
            'is_visible' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->checkRequiredTarget($validator),
            fn (Validator $validator) => $this->checkSubmenu($validator),
        ];
    }

    /**
     * Drops the target that does not match the link type, so stale values are never stored.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        return array_merge($data, [
            'url' => $this->linkTypeIs(MenuItem::TYPE_INTERNAL, MenuItem::TYPE_EXTERNAL) ? ($data['url'] ?? null) : null,
            'media_id' => $this->linkTypeIs(MenuItem::TYPE_MEDIA) ? ($data['media_id'] ?? null) : null,
            'parent_id' => $data['parent_id'] ?? null,
            'is_visible' => (bool) ($data['is_visible'] ?? false),
        ]);
    }

    private function linkTypeIs(string ...$types): bool
    {
        return in_array($this->input('link_type'), $types, true);
    }

    private function checkRequiredTarget(Validator $validator): void
    {
        if ($this->linkTypeIs(MenuItem::TYPE_INTERNAL, MenuItem::TYPE_EXTERNAL) && blank($this->input('url'))) {
            $validator->errors()->add('url', 'A link is required for this type.');
        }

        if ($this->linkTypeIs(MenuItem::TYPE_MEDIA) && blank($this->input('media_id'))) {
            $validator->errors()->add('media_id', 'Choose a file from the library.');
        }
    }

    private function checkSubmenu(Validator $validator): void
    {
        $parentId = $this->input('parent_id');

        if (blank($parentId)) {
            return;
        }

        $current = $this->route('menuItem');
        $parent = MenuItem::find($parentId);

        if ($parent === null) {
            return;
        }

        $problem = match (true) {
            $current instanceof MenuItem && (int) $parentId === $current->id => 'An item cannot be its own parent.',
            $parent->parent_id !== null => 'Submenus can only be one level deep.',
            $parent->location !== $this->input('location') => 'The parent must be in the same menu.',
            $this->linkTypeIs(MenuItem::TYPE_GROUP) => 'A group heading cannot be a submenu item.',
            $current instanceof MenuItem && $current->children()->exists() => 'This item has submenus, so it cannot become a submenu item.',
            default => null,
        };

        if ($problem !== null) {
            $validator->errors()->add('parent_id', $problem);
        }
    }
}
