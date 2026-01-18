<?php

namespace Database\Seeders;

use App\Models\Webhook;
use Illuminate\Database\Seeder;

class WebhookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample webhooks (disabled by default - user should configure their own URLs)
        $webhooks = [
            [
                'name' => 'Slack Notification',
                'url' => 'https://hooks.slack.com/services/YOUR/WEBHOOK/URL',
                'events' => ['vote.created', 'results.published'],
                'is_active' => false,
                'headers' => ['X-Custom-Header' => 'voting-app'],
            ],
            [
                'name' => 'Event Updates',
                'url' => 'https://your-api.example.com/webhooks/events',
                'events' => ['event.created', 'event.updated', 'event.deleted'],
                'is_active' => false,
            ],
            [
                'name' => 'Vote Tracker',
                'url' => 'https://your-api.example.com/webhooks/votes',
                'events' => ['vote.created'],
                'is_active' => false,
            ],
            [
                'name' => 'Voting Status',
                'url' => 'https://your-api.example.com/webhooks/status',
                'events' => ['voting.started', 'voting.ended', 'results.published'],
                'is_active' => false,
            ],
        ];

        foreach ($webhooks as $webhook) {
            Webhook::updateOrCreate(
                ['name' => $webhook['name']],
                $webhook
            );
        }
    }
}
