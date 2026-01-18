<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPromptTemplate extends Model
{
    protected $fillable = [
        'name',
        'context',
        'system_prompt',
        'instructions',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the default prompt for a context
     */
    public static function getDefault(string $context = 'general'): ?self
    {
        return static::where('context', $context)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?? static::where('context', 'general')
                ->where('is_active', true)
                ->where('is_default', true)
                ->first();
    }

    /**
     * Get all active prompts for a context
     */
    public static function getByContext(string $context): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('context', $context)
            ->where('is_active', true)
            ->get();
    }
}
