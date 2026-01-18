<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'entry_id',
        'division_id',
        'category_id',
        'place',
        'rating',
        'base_points',
        'weight_multiplier',
        'voter_ip',
        'voter_fingerprint',
        'deleted_by',
        'deleted_reason',
    ];

    // final_points is a MySQL generated column (base_points * weight_multiplier)

    protected $casts = [
        'place' => 'integer',
        'rating' => 'decimal:1',
        'base_points' => 'decimal:2',
        'weight_multiplier' => 'decimal:2',
        'final_points' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFirstPlace($query)
    {
        return $query->where('place', 1);
    }

    public function scopeSecondPlace($query)
    {
        return $query->where('place', 2);
    }

    public function scopeThirdPlace($query)
    {
        return $query->where('place', 3);
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
