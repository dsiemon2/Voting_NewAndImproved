<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeletionHistory extends Model
{
    protected $table = 'deletion_history';

    protected $fillable = [
        'deletable_type',
        'deletable_id',
        'event_id',
        'item_name',
        'item_type',
        'original_data',
        'related_deletions',
        'deleted_by',
        'deleted_reason',
        'deleted_at',
    ];

    protected $casts = [
        'original_data' => 'array',
        'related_deletions' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function deletable(): MorphTo
    {
        return $this->morphTo();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Record a deletion in history
     */
    public static function recordDeletion(
        Model $model,
        string $itemName,
        string $itemType,
        ?int $eventId = null,
        ?int $deletedBy = null,
        ?string $reason = null,
        ?array $relatedDeletions = null
    ): self {
        return static::create([
            'deletable_type' => get_class($model),
            'deletable_id' => $model->id,
            'event_id' => $eventId,
            'item_name' => $itemName,
            'item_type' => $itemType,
            'original_data' => $model->toArray(),
            'related_deletions' => $relatedDeletions,
            'deleted_by' => $deletedBy,
            'deleted_reason' => $reason,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Get recent deletions for an event
     */
    public static function getRecentForEvent(int $eventId, int $limit = 20)
    {
        return static::where('event_id', $eventId)
            ->orderByDesc('deleted_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get deletions by type
     */
    public static function getByType(string $itemType, int $limit = 50)
    {
        return static::where('item_type', $itemType)
            ->orderByDesc('deleted_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope by deletable type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('deletable_type', $type);
    }

    /**
     * Scope by event
     */
    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }
}
