<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class EventTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'participant_label',
        'entry_label',
        'division_types',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'division_types' => 'array',
    ];

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'event_template_modules')
            ->withPivot(['is_required', 'is_enabled_by_default', 'display_order', 'custom_label', 'configuration'])
            ->orderBy('event_template_modules.display_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get modules that are enabled by default
     */
    public function getEnabledModules(): Collection
    {
        return $this->modules()->wherePivot('is_enabled_by_default', true)->get();
    }

    /**
     * Get required modules
     */
    public function getRequiredModules(): Collection
    {
        return $this->modules()->wherePivot('is_required', true)->get();
    }

    /**
     * Check if template has a specific module
     */
    public function hasModule(string $moduleCode): bool
    {
        return $this->modules()->where('code', $moduleCode)->exists();
    }

    /**
     * Get label for module (custom or default)
     */
    public function getModuleLabel(string $moduleCode): ?string
    {
        $module = $this->modules()->where('code', $moduleCode)->first();
        return $module?->pivot?->custom_label ?? $module?->name;
    }

    /**
     * Get division types for this template
     */
    public function getDivisionTypes(): array
    {
        return $this->division_types ?? [];
    }

    /**
     * Get division type name by code
     */
    public function getDivisionTypeName(string $code): string
    {
        $types = $this->division_types ?? [];
        foreach ($types as $type) {
            if ($type['code'] === $code) {
                return $type['name'];
            }
        }
        return $code; // Fallback to code if not found
    }
}
