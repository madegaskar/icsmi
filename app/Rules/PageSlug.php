<?php

namespace App\Rules;

use App\Support\ReservedSlugs;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A page address: lowercase letters, digits, and single hyphens, and not a path the site already uses.
 */
class PageSlug implements ValidationRule
{
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(self::PATTERN, $value)) {
            $fail('The :attribute may only contain lowercase letters, numbers, and single hyphens.');

            return;
        }

        if (in_array($value, ReservedSlugs::all(), true)) {
            $fail('This address is already used by the site.');
        }
    }
}
