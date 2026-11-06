<?php

namespace App\Notifications;

use App\Models\PublicSubmission;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ConfirmPublicSubmission extends Notification
{
    public function __construct(public PublicSubmission $submission) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Holds only fixed text and the reference: nothing a stranger typed reaches the recipient.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirm your ICSMI submission '.$this->submission->code)
            ->line('A manuscript was submitted to ICSMI using this email address.')
            ->line('Confirm it to activate your author account and start the review process. The link is valid for '.PublicSubmission::CONFIRMATION_DAYS.' days.')
            ->action('Confirm submission', URL::temporarySignedRoute(
                'submission.confirm',
                now()->addDays(PublicSubmission::CONFIRMATION_DAYS),
                ['submission' => $this->submission->id],
            ))
            ->line('If this was not you, ignore this email and the submission will be deleted.');
    }
}
