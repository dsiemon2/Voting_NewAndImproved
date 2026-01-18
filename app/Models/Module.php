<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'route_prefix',
        'display_order',
        'is_core',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'display_order' => 'integer',
    ];

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(EventTemplate::class, 'event_template_modules')
            ->withPivot(['is_required', 'is_enabled_by_default', 'display_order', 'custom_label', 'configuration']);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_modules')
            ->withPivot(['is_enabled', 'custom_label', 'configuration']);
    }

    /**
     * Get route name for this module
     */
    public function getRouteName(string $action = 'index'): string
    {
        return "admin.events.{$this->route_prefix}.{$action}";
    }

    /**
     * Scope to get only active modules
     */
    public function scopeActive($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Scope to get core modules
     */
    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }
}
