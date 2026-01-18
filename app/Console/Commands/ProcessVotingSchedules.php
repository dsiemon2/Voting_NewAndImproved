<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\User;
use App\Models\Webhook;
use App\Notifications\VotingStartedNotification;
use App\Notifications\VotingEndsSoonNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class ProcessVotingSchedules extends Command
{
    protected $signature = 'voting:process-schedules';

    protected $description = 'Process scheduled voting start/end times and send notifications';

    public function handle(): int
    {
        $this->processVotingStarts();
        $this->processVotingEnds();
        $this->processVotingReminders();

        $this->info('Voting schedules processed successfully.');

        return Command::SUCCESS;
    }

    /**
     * Process events where voting just started.
     */
    protected function processVotingStarts(): void
    {
        // Find events that started voting in the last 5 minutes
        $events = Event::where('is_active', true)
            ->whereNotNull('voting_starts_at')
            ->where('voting_starts_at', '>=', now()->subMinutes(5))
            ->where('voting_starts_at', '<=', now())
            ->whereNull('settings->voting_started_notified')
            ->get();

        foreach ($events as $event) {
            $this->info("Processing voting start for: {$event->name}");

            // Dispatch webhook
            Webhook::dispatch('voting.started', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'started_at' => now()->toIso8601String(),
            ]);

            // Send notifications to admin users
            $admins = User::whereHas('role', fn($q) => $q->where('name', 'Administrator'))->get();
            Notification::send($admins, new VotingStartedNotification($event));

            // Mark as notified
            $settings = $event->settings ?? [];
            $settings['voting_started_notified'] = now()->toIso8601String();
            $event->update(['settings' => $settings]);
        }

        if ($events->count()) {
            $this->info("Processed {$events->count()} voting start events.");
        }
    }

    /**
     * Process events where voting just ended.
     */
    protected function processVotingEnds(): void
    {
        // Find events that ended voting in the last 5 minutes
        $events = Event::where('is_active', true)
            ->whereNotNull('voting_ends_at')
            ->where('voting_ends_at', '>=', now()->subMinutes(5))
            ->where('voting_ends_at', '<=', now())
            ->whereNull('settings->voting_ended_notified')
            ->get();

        foreach ($events as $event) {
            $this->info("Processing voting end for: {$event->name}");

            // Dispatch webhook
            Webhook::dispatch('voting.ended', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'ended_at' => now()->toIso8601String(),
                'total_votes' => $event->votes()->count(),
            ]);

            // Auto-publish results if configured
            if ($event->auto_publish_results) {
                Webhook::dispatch('results.published', [
                    'event_id' => $event->id,
                    'event_name' => $event->name,
                    'published_at' => now()->toIso8601String(),
                ]);
            }

            // Mark as notified
            $settings = $event->settings ?? [];
            $settings['voting_ended_notified'] = now()->toIso8601String();
            $event->update(['settings' => $settings]);
        }

        if ($events->count()) {
            $this->info("Processed {$events->count()} voting end events.");
        }
    }

    /**
     * Send reminders for events ending soon.
     */
    protected function processVotingReminders(): void
    {
        // Find events ending in the next 24 hours that haven't been reminded
        $events = Event::where('is_active', true)
            ->whereNotNull('voting_ends_at')
            ->where('voting_ends_at', '>', now())
            ->where('voting_ends_at', '<=', now()->addHours(24))
            ->whereNull('settings->voting_reminder_sent')
            ->get();

        foreach ($events as $event) {
            $hoursRemaining = now()->diffInHours($event->voting_ends_at);
            $timeRemaining = $hoursRemaining > 1 ? "{$hoursRemaining} hours" : '1 hour';

            $this->info("Sending reminder for: {$event->name} ({$timeRemaining} remaining)");

            // Send notifications to admin users
            $admins = User::whereHas('role', fn($q) => $q->where('name', 'Administrator'))->get();
            Notification::send($admins, new VotingEndsSoonNotification($event, $timeRemaining));

            // Mark as reminded
            $settings = $event->settings ?? [];
            $settings['voting_reminder_sent'] = now()->toIso8601String();
            $event->update(['settings' => $settings]);
        }

        if ($events->count()) {
            $this->info("Sent {$events->count()} voting reminders.");
        }
    }
}
