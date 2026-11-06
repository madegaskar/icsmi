<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Renders Markdown with raw HTML removed and unsafe link schemes dropped.
 */
final class SafeMarkdown
{
    public static function render(?string $markdown): HtmlString
    {
        return new HtmlString(Str::markdown($markdown ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }
}
