<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uuid', 'original_name', 'kind', 'mime_type', 'size', 'width', 'height', 'alt_text', 'caption', 'paths', 'uploaded_by'])]
class Media extends Model
{
    public const KIND_IMAGE = 'image';

    public const KIND_PDF = 'pdf';

    public const VARIANT_ORIGINAL = 'original';

    protected $table = 'media';

    /**
     * Route links use the random UUID, never the database id.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paths' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function isImage(): bool
    {
        return $this->kind === self::KIND_IMAGE;
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
