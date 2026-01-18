<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventModule extends Model
{
    protected $fillable = [
        'event_id',
        'module_id',
        'is_enabled',
        'custom_label',
        'configuration',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'configuration' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get display label
     */
    public function getDisplayLabel(): string
    {
        return $this->custom_label ?? $this->module->name;
    }
}
