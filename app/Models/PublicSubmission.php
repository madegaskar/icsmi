<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'title', 'name', 'email', 'phone', 'institution_address', 'abstract', 'keywords', 'path', 'original_name', 'extension', 'size', 'sha256', 'confirmed_at', 'manuscript_id'])]
class PublicSubmission extends Model
{
    public const CONFIRMATION_DAYS = 7;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    public function manuscript(): BelongsTo
    {
        return $this->belongsTo(Manuscript::class);
    }
}
