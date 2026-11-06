<?php

namespace App\Policies;

use App\Enums\ManuscriptStatus;
use App\Enums\ReviewStatus;
use App\Models\ReviewAssignment;
use App\Models\User;

class ReviewAssignmentPolicy
{
    public function view(User $user, ReviewAssignment $assignment): bool
    {
        return $assignment->reviewer_id === $user->id
            && $assignment->status !== ReviewStatus::Cancelled;
    }

    public function respond(User $user, ReviewAssignment $assignment): bool
    {
        return $this->view($user, $assignment)
            && $assignment->status === ReviewStatus::Invited
            && $this->isOpen($assignment);
    }

    public function submit(User $user, ReviewAssignment $assignment): bool
    {
        return $this->view($user, $assignment)
            && $assignment->status === ReviewStatus::Accepted
            && $this->isOpen($assignment);
    }

    private function isOpen(ReviewAssignment $assignment): bool
    {
        return $assignment->isForCurrentRound()
            && $assignment->manuscript->status === ManuscriptStatus::InReview;
    }
}
