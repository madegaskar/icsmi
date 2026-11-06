<?php

namespace App\Http\Requests\Admin;

use App\Models\HomeBlock;
use App\Models\Media;
use App\Rules\SafeLink;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HomeBlockRequest extends FormRequest
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
            'type' => ['required', Rule::in(HomeBlock::TYPES)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:'.self::MAX_SORT_ORDER],
            'is_visible' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'heading' => ['nullable', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'button_label' => ['nullable', 'string', 'max:60'],
            'button_url' => ['nullable', 'string', 'max:255', new SafeLink],
            'desktop_media_id' => ['nullable', 'integer', Rule::exists(Media::class, 'id')->where('kind', Media::KIND_IMAGE)],
            'mobile_media_id' => ['nullable', 'integer', Rule::exists(Media::class, 'id')->where('kind', Media::KIND_IMAGE)],
            'desktop_image' => ['nullable', 'file', 'max:'.intdiv((int) config('media.max_image_bytes'), 1024)],
            'image_alt' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $this->hasFile('desktop_image'))],
            'cover_image' => ['nullable', 'file', 'max:'.intdiv((int) config('media.max_image_bytes'), 1024)],
            'cover_alt' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $this->hasFile('cover_image'))],
            'cover_media_id' => ['nullable', 'integer', Rule::exists(Media::class, 'id')->where('kind', Media::KIND_IMAGE)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->checkRequiredText($validator),
            fn (Validator $validator) => $this->checkHeroIsUnique($validator),
            fn (Validator $validator) => $this->checkButtonPair($validator),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        unset($data['desktop_image'], $data['image_alt'], $data['cover_image'], $data['cover_alt']);

        return array_merge($data, [
            'is_visible' => (bool) ($data['is_visible'] ?? false),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);
    }

    private function checkRequiredText(Validator $validator): void
    {
        $type = $this->input('type');

        if (in_array($type, [HomeBlock::TYPE_HERO, HomeBlock::TYPE_TEXT], true) && blank($this->input('heading'))) {
            $validator->errors()->add('heading', 'A heading is required for this block.');
        }

        if (in_array($type, [HomeBlock::TYPE_TEXT, HomeBlock::TYPE_TABLE], true) && blank($this->input('body'))) {
            $validator->errors()->add('body', 'Text is required for this block.');
        }
    }

    private function checkHeroIsUnique(Validator $validator): void
    {
        if ($this->input('type') !== HomeBlock::TYPE_HERO) {
            return;
        }

        $current = $this->route('homeBlock');
        $otherHero = HomeBlock::where('type', HomeBlock::TYPE_HERO)
            ->when($current instanceof HomeBlock, fn ($query) => $query->whereKeyNot($current->id))
            ->exists();

        if ($otherHero) {
            $validator->errors()->add('type', 'The home page can have only one hero block.');
        }
    }

    private function checkButtonPair(Validator $validator): void
    {
        $hasLabel = filled($this->input('button_label'));
        $hasUrl = filled($this->input('button_url'));

        if ($hasLabel !== $hasUrl) {
            $validator->errors()->add($hasLabel ? 'button_url' : 'button_label', 'A button needs both a label and a link.');
        }
    }
}
