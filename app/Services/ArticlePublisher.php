<?php

namespace App\Services;

use App\Enums\ManuscriptStatus;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Keyword;
use App\Models\Manuscript;
use App\Models\User;
use App\Notifications\ManuscriptStatusChanged;
use App\Support\UniqueSlug;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ArticlePublisher
{
    /**
     * @param  array{issue_id: int, doi?: ?string, first_page?: ?int, last_page?: ?int}  $data
     */
    public function publish(Manuscript $manuscript, User $editor, array $data, UploadedFile $pdf): Article
    {
        if ($manuscript->status !== ManuscriptStatus::Accepted) {
            throw ValidationException::withMessages(['publish' => 'Only accepted manuscripts can be published.']);
        }

        $article = DB::transaction(function () use ($manuscript, $editor, $data, $pdf) {
            $article = Article::create([
                'manuscript_id' => $manuscript->id,
                'issue_id' => $data['issue_id'],
                'slug' => UniqueSlug::for(Article::class, $manuscript->title),
                'title' => $manuscript->title,
                'abstract' => $manuscript->abstract,
                'doi' => $data['doi'] ?? null,
                'first_page' => $data['first_page'] ?? null,
                'last_page' => $data['last_page'] ?? null,
                'pdf_path' => $this->storePdf($pdf),
                'published_at' => now(),
            ]);

            foreach ($manuscript->authors as $position => $author) {
                $article->authors()->create(['name' => $author->name, 'affiliation' => $author->affiliation, 'position' => $position]);
            }

            $this->syncKeywords($article, $manuscript->keywords);

            $from = $manuscript->status;
            $manuscript->update(['status' => ManuscriptStatus::Published]);
            $manuscript->histories()->create([
                'actor_id' => $editor->id,
                'from_status' => $from,
                'to_status' => ManuscriptStatus::Published,
                'created_at' => now(),
            ]);

            return $article;
        });

        AuditLog::record('manuscript.published', $editor->id, ['manuscript_id' => $manuscript->id, 'article_id' => $article->id]);

        rescue(fn () => $manuscript->submitter->notify(new ManuscriptStatusChanged($manuscript, ManuscriptStatus::Published, null)));

        return $article;
    }

    public function storePdf(UploadedFile $pdf): string
    {
        return Storage::disk('local')->putFileAs('articles', $pdf, Str::random(40).'.pdf');
    }

    public function syncKeywords(Article $article, string $keywords): void
    {
        $ids = collect(explode(',', $keywords))
            ->map(fn ($keyword) => Str::limit(trim($keyword), 200, ''))
            ->filter()
            ->map(fn ($keyword) => Keyword::firstOrCreate(['name' => $keyword])->id);

        $article->keywords()->sync($ids);
    }
}
