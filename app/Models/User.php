<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'affiliation', 'country', 'orcid', 'phone', 'mail_status_updates', 'privacy_consent_at', 'mail_announcements'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'privacy_consent_at' => 'datetime',
            'mail_announcements' => 'boolean',
            'mail_status_updates' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'reviewer_id');
    }

    public function hasRole(Role ...$roles): bool
    {
        return $this->roles->contains(fn (UserRole $assigned) => in_array($assigned->role, $roles, true));
    }

    public function assignRole(Role $role): void
    {
        $this->roles()->firstOrCreate(['role' => $role]);
        $this->unsetRelation('roles');
    }
}
