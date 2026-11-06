<?php

namespace App\Policies;

use App\Enums\ManuscriptStatus;
use App\Enums\Role;
use App\Models\Manuscript;
use App\Models\User;

class ManuscriptPolicy
{
    public function view(User $user, Manuscript $manuscript): bool
    {
        return $manuscript->submitter_id === $user->id || $this->manage($user, $manuscript);
    }

    /**
     * Editors may not handle a manuscript they wrote.
     */
    public function manage(User $user, Manuscript $manuscript): bool
    {
        return $user->hasRole(Role::Editor) && ! $manuscript->isAuthoredBy($user);
    }

    public function revise(User $user, Manuscript $manuscript): bool
    {
        return $manuscript->submitter_id === $user->id
            && $manuscript->status === ManuscriptStatus::RevisionRequired;
    }
}
