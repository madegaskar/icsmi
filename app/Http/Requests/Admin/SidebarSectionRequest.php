<?php

namespace App\Http\Requests\Admin;

use App\Models\SidebarSection;
use App\Rules\SafeLink;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SidebarSectionRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:100'],
            'kind' => ['required', Rule::in(SidebarSection::KINDS)],
            'body' => ['nullable', 'string', 'max:5000'],
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
            fn (Validator $validator) => $this->checkBody($validator),
        ];
    }

    /**
     * The visitor section has no body of its own, so anything typed there is dropped.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        return array_merge($data, [
            'is_visible' => (bool) ($data['is_visible'] ?? false),
            'body' => $data['kind'] === SidebarSection::KIND_VISITOR ? null : ($data['body'] ?? null),
        ]);
    }

    private function checkBody(Validator $validator): void
    {
        $kind = $this->input('kind');

        if ($kind === SidebarSection::KIND_VISITOR) {
            return;
        }

        if (blank($this->input('body'))) {
            $validator->errors()->add('body', 'This section needs some text.');

            return;
        }

        if ($kind !== SidebarSection::KIND_LINKS) {
            return;
        }

        foreach (preg_split('/\R/', (string) $this->input('body')) as $line) {
            if (trim($line) === '') {
                continue;
            }

            [$label, $url] = array_map('trim', explode('|', $line, 2)) + [1 => ''];

            if ($label === '' || $url === '') {
                $validator->errors()->add('body', "Each line must be \"Label | address\": {$line}");

                continue;
            }

            if (! SafeLink::accepts($url)) {
                $validator->errors()->add('body', "This address is not allowed: {$url}");
            }
        }
    }
}
