<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VotingEndsSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event,
        public string $timeRemaining = '24 hours'
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
        return (new MailMessage)
            ->subject('Voting Ends Soon - ' . $this->event->name)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Voting for "' . $this->event->name . '" ends in ' . $this->timeRemaining . '!')
            ->line('If you haven\'t voted yet, now is the time.')
            ->line('Voting closes: ' . $this->event->voting_ends_at->format('F j, Y \a\t g:i A'))
            ->action('Vote Now', url('/vote/' . $this->event->id))
            ->line('Don\'t miss your chance to vote!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'voting_ends_soon',
            'event_id' => $this->event->id,
            'event_name' => $this->event->name,
            'time_remaining' => $this->timeRemaining,
            'voting_ends_at' => $this->event->voting_ends_at?->toIso8601String(),
            'message' => 'Voting ends soon for ' . $this->event->name,
        ];
    }
}
