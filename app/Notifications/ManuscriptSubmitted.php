<?php

namespace App\Notifications;

use App\Models\Manuscript;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManuscriptSubmitted extends Notification
{
    public function __construct(public Manuscript $manuscript) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New manuscript '.$this->manuscript->code,
            'message' => 'A new manuscript has been submitted: '.$this->manuscript->title,
            'url' => route('editor.manuscripts.show', $this->manuscript),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New manuscript '.$this->manuscript->code)
            ->line('A new manuscript has been submitted: '.$this->manuscript->title)
            ->action('Open manuscript', route('editor.manuscripts.show', $this->manuscript));
    }
}
