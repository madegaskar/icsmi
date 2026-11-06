<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'action', 'meta', 'ip_address', 'created_at'])]
class AuditLog extends Model
{
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function record(string $action, ?int $userId = null, array $meta = []): self
    {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'meta' => $meta ?: null,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
