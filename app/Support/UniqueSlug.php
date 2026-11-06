<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueSlug
{
    /**
     * @param  class-string<Model>  $model
     * @param  array<int, string>  $reserved  Slugs that must never be handed out.
     */
    public static function for(string $model, string $text, bool $includeTrashed = false, array $reserved = []): string
    {
        $base = Str::limit(Str::slug($text), 200, '') ?: 'item';
        $slug = $base;
        $suffix = 2;

        while (in_array($slug, $reserved, true) || self::exists($model, $slug, $includeTrashed)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $model
     */
    private static function exists(string $model, string $slug, bool $includeTrashed): bool
    {
        $query = $includeTrashed ? $model::withTrashed() : $model::query();

        return $query->where('slug', $slug)->exists();
    }
}
