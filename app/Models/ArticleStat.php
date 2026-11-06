<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['article_id', 'date', 'views', 'downloads'])]
class ArticleStat extends Model
{
    public $timestamps = false;

    /**
     * @return array{views: int, downloads: int}
     */
    public static function totalsFor(Article $article): array
    {
        $totals = self::where('article_id', $article->id)->selectRaw('COALESCE(SUM(views), 0) as views, COALESCE(SUM(downloads), 0) as downloads')->first();

        return [
            'views' => (int) $totals->views,
            'downloads' => (int) $totals->downloads,
        ];
    }

    public static function recordView(Article $article): void
    {
        self::today($article)->increment('views');
    }

    public static function recordDownload(Article $article): void
    {
        self::today($article)->increment('downloads');
    }

    private static function today(Article $article): self
    {
        return self::firstOrCreate(['article_id' => $article->id, 'date' => now()->toDateString()]);
    }
}
