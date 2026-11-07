<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Builds the "Home / Page" trail shown above the content of every inner public page.
 */
final class Breadcrumbs
{
    private const HOME_LABEL = 'Home';

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    public static function for(Request $request, ?string $title): array
    {
        $path = trim($request->path(), '/');

        if ($path === '') {
            return [];
        }

        $label = $title ?: Str::headline(Str::afterLast($path, '/'));

        return [
            ['label' => self::HOME_LABEL, 'url' => url('/')],
            ['label' => $label, 'url' => null],
        ];
    }
}
