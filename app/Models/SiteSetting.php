<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Small key and value settings that admins change from the workspace.
 */
#[Fillable(['key', 'value'])]
class SiteSetting extends Model
{
    public const REGISTRATION_OPEN = 'registration_open';

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * New accounts can register unless an admin has closed registration. Open by default.
     */
    public static function registrationOpen(): bool
    {
        return static::get(self::REGISTRATION_OPEN, '1') === '1';
    }
}
