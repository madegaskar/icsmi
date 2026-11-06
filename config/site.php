<?php

return [

    /*
    | UUID of the Media Library image used as the header banner background.
    | Leave empty for a plain background.
    */
    'header_banner_uuid' => env('HEADER_BANNER_UUID'),

    /*
    | Asks search engines not to index the site (staging and preview servers).
    | Adds X-Robots-Tag to every response and serves a robots.txt that blocks crawling.
    */
    'noindex' => filter_var(env('SITE_NOINDEX', false), FILTER_VALIDATE_BOOLEAN),

];
