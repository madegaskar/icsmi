<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewInvitation extends Notification
{
    public function __construct(public ReviewAssignment $assignment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Review invitation '.$this->assignment->manuscript->code)
            ->line('You have been invited to review: '.$this->assignment->manuscript->title)
            ->line('Review due by '.$this->assignment->due_at->format('j F Y').'.')
            ->action('Respond to invitation', route('reviewer.assignments.show', $this->assignment));
    }
}
