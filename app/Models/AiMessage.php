<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'ai_conversation_id',
        'role',
        'content',
        'type',
        'metadata',
        'tokens_used',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tokens_used' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    /**
     * Check if this is a user message
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if this is an assistant message
     */
    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Get visual aids from metadata
     */
    public function getVisualAids(): array
    {
        return $this->metadata['visualAids'] ?? [];
    }

    /**
     * Get wizard state from metadata
     */
    public function getWizardState(): ?array
    {
        return $this->metadata['wizardState'] ?? null;
    }
}
