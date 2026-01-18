<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VotingPlaceConfig extends Model
{
    protected $fillable = [
        'voting_type_id',
        'place',
        'points',
        'label',
        'color',
        'icon',
    ];

    protected $casts = [
        'place' => 'integer',
        'points' => 'decimal:2',
    ];

    public function votingType(): BelongsTo
    {
        return $this->belongsTo(VotingType::class);
    }

    /**
     * Get display label (with fallback)
     */
    public function getDisplayLabel(): string
    {
        return $this->label ?? config("voting.place_labels.{$this->place}", "{$this->place}th Place");
    }

    /**
     * Get display color (with fallback)
     */
    public function getDisplayColor(): string
    {
        return $this->color ?? config("voting.place_colors.{$this->place}", '#6B7280');
    }
}
