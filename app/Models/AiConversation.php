<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'event_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class)->orderBy('created_at');
    }

    /**
     * Get or create a conversation for the current session
     */
    public static function getOrCreate(string $sessionId, ?int $userId = null, ?int $eventId = null): self
    {
        $conversation = static::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();

        if (!$conversation) {
            $conversation = static::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'event_id' => $eventId,
                'status' => 'active',
            ]);
        }

        // Update event if changed
        if ($eventId && $conversation->event_id !== $eventId) {
            $conversation->update(['event_id' => $eventId]);
        }

        return $conversation;
    }

    /**
     * Get recent messages for context
     */
    public function getRecentMessages(int $limit = 10): array
    {
        return $this->messages()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();
    }

    /**
     * Add a message to the conversation
     */
    public function addMessage(string $role, string $content, string $type = 'text', array $metadata = []): AiMessage
    {
        return $this->messages()->create([
            'role' => $role,
            'content' => $content,
            'type' => $type,
            'metadata' => $metadata,
        ]);
    }
}
