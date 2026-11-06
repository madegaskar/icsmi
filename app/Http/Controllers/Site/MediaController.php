<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function show(Media $media, string $variant): Response
    {
        $path = $media->paths[$variant] ?? null;

        abort_if($path === null, 404);

        $disposition = $media->kind === Media::KIND_PDF ? 'attachment' : 'inline';

        return Storage::disk(config('media.disk'))->response($path, $media->original_name, [
            'Content-Type' => $media->mime_type,
            'Cache-Control' => 'public, max-age='.config('media.cache_seconds').', immutable',
            'X-Content-Type-Options' => 'nosniff',
        ], $disposition);
    }
}
