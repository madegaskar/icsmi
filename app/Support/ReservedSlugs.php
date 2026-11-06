<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * First path segments already used by the site's own routes, such as "admin" or "current".
 * A page at one of these addresses would never be reached, so they are not allowed as slugs.
 */
final class ReservedSlugs
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => explode('/', ltrim($route->uri(), '/'))[0])
            ->reject(fn (string $segment) => $segment === '' || str_starts_with($segment, '{'))
            ->unique()
            ->values()
            ->all();
    }
}
