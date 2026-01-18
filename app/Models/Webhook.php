<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Webhook extends Model
{
    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'headers',
        'is_active',
        'retry_count',
        'timeout',
        'last_triggered_at',
        'last_status',
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * Available webhook events.
     */
    public const EVENTS = [
        'event.created' => 'Event Created',
        'event.updated' => 'Event Updated',
        'event.deleted' => 'Event Deleted',
        'vote.created' => 'Vote Cast',
        'entry.created' => 'Entry Created',
        'participant.created' => 'Participant Created',
        'results.published' => 'Results Published',
        'voting.started' => 'Voting Started',
        'voting.ended' => 'Voting Ended',
    ];

    /**
     * Get logs for this webhook.
     */
    public function logs()
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Check if webhook listens to a specific event.
     */
    public function listensTo(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }

    /**
     * Trigger webhook with payload.
     */
    public function trigger(string $event, array $data): ?WebhookLog
    {
        if (!$this->is_active || !$this->listensTo($event)) {
            return null;
        }

        $payload = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];

        // Add signature if secret is set
        if ($this->secret) {
            $payload['signature'] = hash_hmac('sha256', json_encode($data), $this->secret);
        }

        // Create log entry
        $log = $this->logs()->create([
            'event' => $event,
            'payload' => $payload,
            'status' => 'pending',
        ]);

        // Send webhook asynchronously
        try {
            $headers = array_merge(
                ['Content-Type' => 'application/json'],
                $this->headers ?? []
            );

            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->post($this->url, $payload);

            $log->update([
                'response_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 5000),
                'status' => $response->successful() ? 'success' : 'failed',
                'sent_at' => now(),
            ]);

            $this->update([
                'last_triggered_at' => now(),
                'last_status' => $response->successful() ? 'success' : 'failed',
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook failed', [
                'webhook_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'response_body' => $e->getMessage(),
                'status' => 'failed',
                'sent_at' => now(),
            ]);

            $this->update([
                'last_triggered_at' => now(),
                'last_status' => 'failed',
            ]);
        }

        return $log;
    }

    /**
     * Dispatch webhooks for an event.
     */
    public static function dispatch(string $event, array $data): void
    {
        $webhooks = self::where('is_active', true)->get();

        foreach ($webhooks as $webhook) {
            if ($webhook->listensTo($event)) {
                $webhook->trigger($event, $data);
            }
        }
    }
}
