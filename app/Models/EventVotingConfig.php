<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventVotingConfig extends Model
{
    protected $fillable = [
        'event_id',
        'voting_type_id',
        'max_votes_per_user',
        'max_votes_per_entry',
        'allow_self_voting',
        'voting_starts_at',
        'voting_ends_at',
        'show_live_results',
        'show_vote_counts',
        'show_percentages',
        'place_overrides',
    ];

    protected $casts = [
        'max_votes_per_user' => 'integer',
        'max_votes_per_entry' => 'integer',
        'allow_self_voting' => 'boolean',
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'show_live_results' => 'boolean',
        'show_vote_counts' => 'boolean',
        'show_percentages' => 'boolean',
        'place_overrides' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function votingType(): BelongsTo
    {
        return $this->belongsTo(VotingType::class);
    }

    /**
     * Get points for a place, considering overrides
     */
    public function getPointsForPlace(int $place): float
    {
        if ($this->place_overrides && isset($this->place_overrides[$place])) {
            return (float) $this->place_overrides[$place];
        }

        return $this->votingType->getPointsForPlace($place);
    }

    /**
     * Get all place configs (with overrides applied)
     */
    public function getPlaceConfigs(): array
    {
        $baseConfigs = $this->votingType->getPlacesArray();

        if (!$this->place_overrides) {
            return $baseConfigs;
        }

        return array_map(function ($config) {
            if (isset($this->place_overrides[$config['place']])) {
                $config['points'] = (float) $this->place_overrides[$config['place']];
            }
            return $config;
        }, $baseConfigs);
    }

    /**
     * Check if voting is currently open
     */
    public function isVotingOpen(): bool
    {
        $now = now();

        if ($this->voting_starts_at && $now->lt($this->voting_starts_at)) {
            return false;
        }

        if ($this->voting_ends_at && $now->gt($this->voting_ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if voting has ended
     */
    public function hasVotingEnded(): bool
    {
        return $this->voting_ends_at && now()->gt($this->voting_ends_at);
    }

    /**
     * Check if voting hasn't started yet
     */
    public function isVotingPending(): bool
    {
        return $this->voting_starts_at && now()->lt($this->voting_starts_at);
    }
}
