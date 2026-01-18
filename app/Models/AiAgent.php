<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAgent extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'system_prompt',
        'personality',
        'temperature',
        'model',
        'capabilities',
        'is_default',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'temperature' => 'float',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the default agent.
     */
    public static function getDefault(): ?self
    {
        return self::where('is_default', true)
            ->where('is_active', true)
            ->first() ?? self::where('is_active', true)->first();
    }

    /**
     * Get all active agents.
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Set this agent as default.
     */
    public function setAsDefault(): void
    {
        self::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    /**
     * Check if agent has a capability.
     */
    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    /**
     * Get the full system prompt with personality adjustments.
     */
    public function getFullSystemPrompt(): string
    {
        $personalityPrefix = match ($this->personality) {
            'friendly' => "You are a friendly and approachable assistant. Be warm, use conversational language, and make users feel comfortable. ",
            'concise' => "You are a concise and efficient assistant. Give direct answers without unnecessary elaboration. ",
            'creative' => "You are a creative and imaginative assistant. Think outside the box and offer innovative suggestions. ",
            default => "You are a professional and helpful assistant. Be clear, accurate, and thorough in your responses. ",
        };

        return $personalityPrefix . $this->system_prompt;
    }
}
