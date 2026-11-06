<?php

namespace App\Notifications;

use App\Enums\ManuscriptStatus;
use App\Models\Manuscript;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManuscriptStatusChanged extends Notification
{
    public function __construct(public Manuscript $manuscript, public ManuscriptStatus $status, public ?string $note) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ($notifiable->mail_status_updates ?? true) ? ['mail', 'database'] : ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Manuscript '.$this->manuscript->code.': '.$this->status->label(),
            'message' => '"'.$this->manuscript->title.'" is now '.$this->status->label().'.'.($this->note ? ' Note: '.$this->note : ''),
            'url' => route('author.manuscripts.show', $this->manuscript),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Manuscript '.$this->manuscript->code.': '.$this->status->label())
            ->line('The status of "'.$this->manuscript->title.'" is now '.$this->status->label().'.');

        if ($this->note) {
            $message->line('Editor note: '.$this->note);
        }

        return $message->action('View manuscript', route('author.manuscripts.show', $this->manuscript));
    }
}
