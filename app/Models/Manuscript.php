<?php

namespace App\Models;

use App\Enums\ManuscriptStatus;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['code', 'submitter_id', 'title', 'abstract', 'keywords', 'status', 'current_version', 'submitted_at', 'decided_at'])]
class Manuscript extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ManuscriptStatus::class,
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_id');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(ManuscriptAuthor::class)->orderBy('position');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ManuscriptFile::class)->orderByDesc('version')->orderBy('kind');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(StatusHistory::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class)->orderByDesc('round')->orderBy('id');
    }

    public function isAuthoredBy(User $user): bool
    {
        return $this->submitter_id === $user->id
            || $this->authors->contains(fn (ManuscriptAuthor $author) => strcasecmp($author->email, $user->email) === 0);
    }

    public function scopeNotAuthoredBy(Builder $query, User $user): void
    {
        $query->where('submitter_id', '!=', $user->id)
            ->whereDoesntHave('authors', fn (Builder $authors) => $authors->whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)]));
    }

    public function article(): HasOne
    {
        return $this->hasOne(Article::class);
    }

    public function publicSubmission(): HasOne
    {
        return $this->hasOne(PublicSubmission::class);
    }

    public function hasCompletedReviewThisRound(): bool
    {
        return $this->assignments()
            ->where('round', $this->current_version)
            ->where('status', ReviewStatus::Completed)
            ->exists();
    }

    /**
     * Completed reviews the author may read: earlier rounds always, the current round once decided.
     *
     * @return Collection<int, ReviewAssignment>
     */
    public function reviewsVisibleToAuthor(): Collection
    {
        $currentRoundDecided = in_array($this->status, [
            ManuscriptStatus::RevisionRequired,
            ManuscriptStatus::Accepted,
            ManuscriptStatus::Rejected,
            ManuscriptStatus::Published,
        ], true);

        return $this->assignments()
            ->where('status', ReviewStatus::Completed)
            ->where('round', $currentRoundDecided ? '<=' : '<', $this->current_version)
            ->whereNotNull('comments_for_author')
            ->get(['id', 'round', 'comments_for_author']);
    }
}
