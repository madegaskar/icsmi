<?php

namespace App\Enums;

enum ManuscriptStatus: string
{
    case Submitted = 'submitted';
    case Screening = 'screening';
    case InReview = 'in_review';
    case RevisionRequired = 'revision_required';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Screening => 'Initial Screening',
            self::InReview => 'In Review',
            self::RevisionRequired => 'Revision Required',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
            self::Published => 'Published',
        };
    }

    /**
     * Statuses an editor may move a manuscript to from this one.
     * Revisions return through the author's upload, publishing through the issue tools.
     *
     * @return list<self>
     */
    public function editorTransitions(): array
    {
        return match ($this) {
            self::Submitted => [self::Screening, self::Rejected],
            self::Screening => [self::InReview, self::RevisionRequired, self::Rejected],
            self::InReview => [self::RevisionRequired, self::Accepted, self::Rejected],
            default => [],
        };
    }
}
