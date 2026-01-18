<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoterWeightClass extends Model
{
    protected $fillable = [
        'voting_type_id',
        'name',
        'weight_multiplier',
        'description',
        'requires_approval',
    ];

    protected $casts = [
        'weight_multiplier' => 'decimal:2',
        'requires_approval' => 'boolean',
    ];

    public function votingType(): BelongsTo
    {
        return $this->belongsTo(VotingType::class);
    }

    public function userAssignments(): HasMany
    {
        return $this->hasMany(UserVoterClass::class);
    }
}
