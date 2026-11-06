<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects invisible control characters. Tabs and line breaks are allowed.
 */
class NoControlCharacters implements ValidationRule
{
    private const CONTROL_CHARACTERS = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && preg_match(self::CONTROL_CHARACTERS, $value)) {
            $fail('The :attribute contains characters that cannot be shown.');
        }
    }
}
