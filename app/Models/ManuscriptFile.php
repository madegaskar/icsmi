<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['uploaded_by', 'version', 'kind', 'path', 'original_name', 'extension', 'size', 'sha256'])]
class ManuscriptFile extends Model
{
    public const KIND_BLINDED = 'blinded';

    public const KIND_FULL = 'full';

    public const MIME_TYPES = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function manuscript(): BelongsTo
    {
        return $this->belongsTo(Manuscript::class);
    }

    /**
     * A neutral download name that never carries the uploader's file name.
     */
    public function downloadName(): string
    {
        return $this->manuscript->code.'-v'.$this->version.'-'.$this->kind.'.'.$this->extension;
    }
}
