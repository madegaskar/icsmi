<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Splits page Markdown into titled sections ("## "), each with optional subsections ("### ").
 * Text before the first "## " heading becomes a section with no title.
 */
final class MarkdownSections
{
    private const SECTION_PREFIX = '## ';

    private const SUBSECTION_PREFIX = '### ';

    /**
     * @return Collection<int, array{title: string, intro: string, subsections: Collection<int, array{title: string, body: string}>}>
     */
    public static function parse(string $markdown): Collection
    {
        return collect(preg_split('/(?=^'.preg_quote(self::SECTION_PREFIX, '/').')/m', $markdown, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $chunk) => self::section($chunk))
            ->values();
    }

    /**
     * @return array{title: string, intro: string, subsections: Collection<int, array{title: string, body: string}>}
     */
    private static function section(string $chunk): array
    {
        $title = '';
        $rest = $chunk;

        if (str_starts_with($chunk, self::SECTION_PREFIX)) {
            [$heading, $rest] = array_pad(explode("\n", $chunk, 2), 2, '');
            $title = trim(substr($heading, strlen(self::SECTION_PREFIX)));
        }

        $intro = '';
        $subsections = collect();

        foreach (preg_split('/(?=^'.preg_quote(self::SUBSECTION_PREFIX, '/').')/m', $rest, -1, PREG_SPLIT_NO_EMPTY) as $piece) {
            if (str_starts_with($piece, self::SUBSECTION_PREFIX)) {
                [$heading, $body] = array_pad(explode("\n", $piece, 2), 2, '');
                $subsections->push(['title' => trim(substr($heading, strlen(self::SUBSECTION_PREFIX))), 'body' => $body]);
            } else {
                $intro .= $piece;
            }
        }

        return ['title' => $title, 'intro' => trim($intro), 'subsections' => $subsections->values()];
    }
}
