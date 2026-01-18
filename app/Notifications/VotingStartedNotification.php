<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VotingStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Voting Now Open - ' . $this->event->name)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Voting has started for "' . $this->event->name . '"!')
            ->line('Cast your vote now before voting closes.');

        if ($this->event->voting_ends_at) {
            $mail->line('Voting ends: ' . $this->event->voting_ends_at->format('F j, Y \a\t g:i A'));
        }

        return $mail
            ->action('Vote Now', url('/vote/' . $this->event->id))
            ->line('Your vote matters!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'voting_started',
            'event_id' => $this->event->id,
            'event_name' => $this->event->name,
            'voting_ends_at' => $this->event->voting_ends_at?->toIso8601String(),
            'message' => 'Voting started for ' . $this->event->name,
        ];
    }
}
