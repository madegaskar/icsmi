<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\User;
use App\Support\ReservedSlugs;
use App\Support\UniqueSlug;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Saves pages and keeps a revision of every saved state. Revisions are never edited or removed.
 */
final class PageEditor
{
    public const VISIBILITY_DRAFT = 'draft';

    public const VISIBILITY_PUBLISH = 'publish';

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $editor): Page
    {
        return DB::transaction(function () use ($data, $editor) {
            $page = Page::create([
                'title' => $data['title'],
                'slug' => $this->slugFor($data),
                'body' => $data['body'],
                'published_at' => $this->publishedAt($data, null),
            ]);

            $this->snapshot($page, $editor);
            AuditLog::record('page.created', $editor->id, ['page_id' => $page->id]);

            return $page;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Page $page, array $data, User $editor): Page
    {
        return DB::transaction(function () use ($page, $data, $editor) {
            $page->update([
                'title' => $data['title'],
                'slug' => blank($data['slug'] ?? null) ? $page->slug : $data['slug'],
                'body' => $data['body'],
                'published_at' => $this->publishedAt($data, $page),
            ]);

            $this->snapshot($page, $editor);
            AuditLog::record('page.updated', $editor->id, ['page_id' => $page->id]);

            return $page;
        });
    }

    /**
     * Puts back the title and body of an older revision. The address and visibility stay as they are.
     */
    public function restore(Page $page, PageRevision $revision, User $editor): Page
    {
        return DB::transaction(function () use ($page, $revision, $editor) {
            $page->update([
                'title' => $revision->title,
                'body' => $revision->body,
            ]);

            $this->snapshot($page, $editor);
            AuditLog::record('page.restored', $editor->id, ['page_id' => $page->id, 'revision_id' => $revision->id]);

            return $page;
        });
    }

    public function delete(Page $page, User $editor): void
    {
        $page->delete();

        AuditLog::record('page.deleted', $editor->id, ['page_id' => $page->id]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function slugFor(array $data): string
    {
        if (! blank($data['slug'] ?? null)) {
            return $data['slug'];
        }

        return UniqueSlug::for(Page::class, $data['title'], includeTrashed: true, reserved: ReservedSlugs::all());
    }

    /**
     * Without a visibility choice, the current state is kept. Publishing without a time publishes now.
     *
     * @param  array<string, mixed>  $data
     */
    private function publishedAt(array $data, ?Page $current): ?CarbonInterface
    {
        if (! array_key_exists('visibility', $data)) {
            return $current?->published_at;
        }

        if ($data['visibility'] !== self::VISIBILITY_PUBLISH) {
            return null;
        }

        return blank($data['publish_at'] ?? null) ? now() : Carbon::parse($data['publish_at']);
    }

    private function snapshot(Page $page, User $editor): void
    {
        PageRevision::create([
            'page_id' => $page->id,
            'user_id' => $editor->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'body' => $page->body,
            'published_at' => $page->published_at,
        ]);
    }
}
