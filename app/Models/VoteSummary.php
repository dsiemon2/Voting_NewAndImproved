<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoteSummary extends Model
{
    protected $fillable = [
        'event_id',
        'entry_id',
        'division_id',
        'category_id',
        'total_points',
        'vote_count',
        'first_place_count',
        'second_place_count',
        'third_place_count',
        'average_rating',
        'ranking',
    ];

    protected $casts = [
        'total_points' => 'decimal:2',
        'vote_count' => 'integer',
        'first_place_count' => 'integer',
        'second_place_count' => 'integer',
        'third_place_count' => 'integer',
        'average_rating' => 'decimal:2',
        'ranking' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForDivision($query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    public function scopeTopRanked($query, $limit = 10)
    {
        return $query->orderByDesc('total_points')->limit($limit);
    }
}
