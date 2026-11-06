<?php

namespace App\Models;

use App\Enums\ReviewRecommendation;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reviewer_id', 'assigned_by', 'round', 'status', 'due_at', 'responded_at', 'completed_at', 'recommendation', 'comments_for_author', 'comments_for_editor'])]
class ReviewAssignment extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'recommendation' => ReviewRecommendation::class,
            'due_at' => 'date',
            'responded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function manuscript(): BelongsTo
    {
        return $this->belongsTo(Manuscript::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isForCurrentRound(): bool
    {
        return $this->round === $this->manuscript->current_version;
    }
}
