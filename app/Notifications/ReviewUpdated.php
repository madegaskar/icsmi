<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewUpdated extends Notification
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
            ->subject('Review '.$this->assignment->status->value.': '.$this->assignment->manuscript->code)
            ->line('A reviewer has updated their assignment: '.$this->assignment->status->label().'.')
            ->action('Open manuscript', route('editor.manuscripts.show', $this->assignment->manuscript));
    }
}
