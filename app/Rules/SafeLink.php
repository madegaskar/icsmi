<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Accepts only a path on this site, an https:// link, or a mailto: link.
 * Anything else, such as javascript:, data:, or http://, is rejected.
 */
class SafeLink implements ValidationRule
{
    private const INTERNAL_PATH = '@^/(?!/)[A-Za-z0-9/._?=&%#-]*\z@';

    private const EXTERNAL_LINK = '@^(https://|mailto:)[^\s<>"\'\\\\]+\z@';

    public static function accepts(string $value): bool
    {
        return preg_match(self::INTERNAL_PATH, $value) === 1 || preg_match(self::EXTERNAL_LINK, $value) === 1;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::accepts($value)) {
            $fail('The :attribute must be a page path on this site, an https:// link, or a mailto: link.');
        }
    }
}
