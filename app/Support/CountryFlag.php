<?php

namespace App\Support;

/**
 * Turns a two-letter country code into its flag emoji and English country name.
 */
final class CountryFlag
{
    private const UNKNOWN_FLAG = "\u{1F310}";

    private const REGIONAL_INDICATOR_A = 0x1F1E6;

    public static function emoji(?string $code): string
    {
        if (! self::isValid($code)) {
            return self::UNKNOWN_FLAG;
        }

        return implode('', array_map(
            fn (string $letter) => mb_chr(self::REGIONAL_INDICATOR_A + ord($letter) - ord('A'), 'UTF-8'),
            str_split($code),
        ));
    }

    public static function name(?string $code): string
    {
        if (! self::isValid($code)) {
            return 'Unknown';
        }

        return \Locale::getDisplayRegion('-'.$code, 'en') ?: $code;
    }

    private static function isValid(?string $code): bool
    {
        return is_string($code) && preg_match('/^[A-Z]{2}$/', $code) === 1;
    }
}
