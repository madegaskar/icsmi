<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Renders schema.org data as a JSON-LD script tag. Tag characters are escaped
 * so user-written text can never close the script element early.
 */
final class JsonLd
{
    private const FLAGS = JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

    /**
     * @param  array<string, mixed>  $data
     */
    public static function script(array $data): HtmlString
    {
        $json = json_encode(['@context' => 'https://schema.org'] + $data, self::FLAGS);

        return new HtmlString('<script type="application/ld+json">'.$json.'</script>');
    }
}
