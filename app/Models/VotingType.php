<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VotingType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'is_system',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function placeConfigs(): HasMany
    {
        return $this->hasMany(VotingPlaceConfig::class)->orderBy('place');
    }

    public function voterWeightClasses(): HasMany
    {
        return $this->hasMany(VoterWeightClass::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function eventVotingConfigs(): HasMany
    {
        return $this->hasMany(EventVotingConfig::class);
    }

    /**
     * Get points for a specific place
     */
    public function getPointsForPlace(int $place): float
    {
        return $this->placeConfigs()
            ->where('place', $place)
            ->value('points') ?? 0;
    }

    /**
     * Get all place configurations as array
     */
    public function getPlacesArray(): array
    {
        return $this->placeConfigs->map(fn($config) => [
            'place' => $config->place,
            'points' => (float) $config->points,
            'label' => $config->label,
            'color' => $config->color,
            'icon' => $config->icon,
        ])->toArray();
    }

    /**
     * Get number of places for this voting type
     */
    public function getPlaceCount(): int
    {
        return $this->placeConfigs()->count();
    }

    /**
     * Check if this is a ranked voting type
     */
    public function isRanked(): bool
    {
        return $this->category === 'ranked';
    }

    /**
     * Check if this is an approval voting type
     */
    public function isApproval(): bool
    {
        return $this->category === 'approval';
    }

    /**
     * Check if this is a weighted voting type
     */
    public function isWeighted(): bool
    {
        return $this->category === 'weighted';
    }

    /**
     * Check if this is a rating voting type
     */
    public function isRating(): bool
    {
        return $this->category === 'rating';
    }
}
