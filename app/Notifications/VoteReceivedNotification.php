<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VoteReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event,
        public int $voteCount
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
            ->subject('Vote Received - ' . $this->event->name)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('A new vote has been cast in your event "' . $this->event->name . '".')
            ->line('Current total votes: ' . $this->voteCount)
            ->action('View Results', url('/results/' . $this->event->id))
            ->line('Thank you for using our voting application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vote_received',
            'event_id' => $this->event->id,
            'event_name' => $this->event->name,
            'vote_count' => $this->voteCount,
            'message' => 'New vote received in ' . $this->event->name,
        ];
    }
}
