<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'division_id',
        'category_id',
        'participant_id',
        'entry_number',
        'name',
        'description',
        'image_path',
        'custom_fields',
        'is_active',
        'deleted_by',
        'deleted_reason',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function voteSummary(): HasOne
    {
        return $this->hasOne(VoteSummary::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDivision($query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Get display number (entry_number or generated)
     */
    public function getDisplayNumber(): string
    {
        if ($this->entry_number) {
            return $this->entry_number;
        }

        $prefix = $this->division?->code ?? '';
        return $prefix . $this->id;
    }

    /**
     * Get total points from votes
     */
    public function getTotalPoints(): float
    {
        return $this->votes()->sum('final_points');
    }

    /**
     * Get vote count
     */
    public function getVoteCount(): int
    {
        return $this->votes()->count();
    }

    /**
     * Get custom field value
     */
    public function getCustomField(string $key, $default = null)
    {
        return $this->custom_fields[$key] ?? $default;
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
