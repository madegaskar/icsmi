<?php

namespace App\Policies;

use App\Enums\ReviewStatus;
use App\Enums\Role;
use App\Models\ManuscriptFile;
use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ManuscriptFilePolicy
{
    public function download(User $user, ManuscriptFile $file): bool
    {
        if (Gate::forUser($user)->allows('view', $file->manuscript)) {
            return true;
        }

        return $file->kind === ManuscriptFile::KIND_BLINDED
            && $user->hasRole(Role::Reviewer)
            && ReviewAssignment::query()
                ->where('manuscript_id', $file->manuscript_id)
                ->where('reviewer_id', $user->id)
                ->where('round', $file->version)
                ->whereIn('status', [ReviewStatus::Accepted, ReviewStatus::Completed])
                ->exists();
    }
}
