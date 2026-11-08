<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media library
    |--------------------------------------------------------------------------
    |
    | Files live on the private disk and are only reachable through the
    | media route. Names are random, so cached responses can be kept for a long
    | time without ever serving the wrong file.
    |
    */

    'disk' => 'local',

    'directory' => 'media',

    'max_image_bytes' => 5 * 1024 * 1024,
    'max_image_pixels' => 24_000_000,

    'max_pdf_bytes' => 20 * 1024 * 1024,

    /*
    | Allowed image types, keyed by the MIME type detected from the file's
    | content. The value is the extension used when the file is stored.
    */
    'image_types' => [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ],

    'jpeg_quality' => 85,

    'webp_quality' => 85,

    /*
    | Maximum width of each stored image variant. Images are never enlarged.
    */
    'variant_widths' => [
        'desktop' => 1600,
        'mobile' => 800,
    ],

    'cache_seconds' => 60 * 60 * 24 * 365,

];
