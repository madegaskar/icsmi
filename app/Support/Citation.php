<?php

namespace App\Support;

use App\Models\Article;
use Illuminate\Support\Str;

class Citation
{
    public static function apa(Article $article): string
    {
        $authors = $article->authors->map(fn ($author) => self::apaName($author->name))->values();

        $names = match (true) {
            $authors->count() <= 1 => $authors->first() ?? '',
            default => $authors->slice(0, -1)->join(', ').', & '.$authors->last(),
        };

        $pages = $article->pages() ? ' (pp. '.$article->pages().')' : '';
        $doi = $article->doi ? ' https://doi.org/'.$article->doi : '';

        return trim($names.' ('.$article->issue->year.'). '.$article->title.'. In '.$article->issue->title.$pages.'.'.$doi);
    }

    public static function bibtex(Article $article): string
    {
        $firstAuthor = $article->authors->first()?->name ?? 'icsmi';
        $key = Str::slug(Str::afterLast($firstAuthor, ' ')).$article->issue->year.$article->id;

        $fields = array_filter([
            'title' => $article->title,
            'author' => $article->authors->pluck('name')->join(' and '),
            'booktitle' => $article->issue->title,
            'year' => (string) $article->issue->year,
            'pages' => $article->first_page ? str_replace('–', '--', $article->pages()) : null,
            'doi' => $article->doi,
        ]);

        $lines = collect($fields)->map(fn ($value, $name) => '  '.$name.' = {'.self::bibtexValue($value).'}');

        return '@inproceedings{'.$key.",\n".$lines->join(",\n")."\n}";
    }

    private static function apaName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $family = array_pop($parts);

        if ($parts === []) {
            return $family;
        }

        $initials = array_map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)).'.', $parts);

        return $family.', '.implode(' ', $initials);
    }

    private static function bibtexValue(string $value): string
    {
        return str_replace(['\\', '{', '}'], '', $value);
    }
}
