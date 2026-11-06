<?php

namespace App\Http\Requests;

use App\Rules\ManuscriptDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreManuscriptRequest extends FormRequest
{
    public const MAX_AUTHORS = 8;

    protected function prepareForValidation(): void
    {
        $authors = $this->input('authors');

        if (is_array($authors)) {
            $this->merge(['authors' => array_values(array_filter($authors, fn ($author) => is_array($author) && array_filter($author, fn ($value) => filled($value)) !== []))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'max:5000'],
            'keywords' => ['required', 'string', 'max:500'],
            'authors' => ['required', 'array', 'min:1', 'max:'.self::MAX_AUTHORS],
            'authors.*.name' => ['required', 'string', 'max:255'],
            'authors.*.email' => ['required', 'string', 'email', 'max:255'],
            'authors.*.affiliation' => ['required', 'string', 'max:255'],
            'blinded_file' => ['required', 'file', 'max:20480', new ManuscriptDocument],
            'full_file' => ['nullable', 'file', 'max:20480', new ManuscriptDocument],
        ];
    }
}
