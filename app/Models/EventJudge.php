<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventJudge extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'vote_weight',
        'title',
        'bio',
        'is_active',
        'can_see_results',
        'can_vote_own_division',
    ];

    protected $casts = [
        'vote_weight' => 'decimal:2',
        'is_active' => 'boolean',
        'can_see_results' => 'boolean',
        'can_vote_own_division' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this judge can vote
     */
    public function canVote(): bool
    {
        return $this->is_active && $this->user->is_active;
    }

    /**
     * Get the effective vote weight
     */
    public function getEffectiveWeight(): float
    {
        return $this->is_active ? (float) $this->vote_weight : 1.0;
    }
}
