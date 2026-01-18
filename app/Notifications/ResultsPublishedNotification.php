<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResultsPublishedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Results Published - ' . $this->event->name)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('The results for "' . $this->event->name . '" have been published!')
            ->line('Thank you to everyone who participated.')
            ->action('View Results', url('/results/' . $this->event->id))
            ->line('Congratulations to all the winners!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'results_published',
            'event_id' => $this->event->id,
            'event_name' => $this->event->name,
            'message' => 'Results published for ' . $this->event->name,
        ];
    }
}
