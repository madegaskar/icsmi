<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Invited = 'invited';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
