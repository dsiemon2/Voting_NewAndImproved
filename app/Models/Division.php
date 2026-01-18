<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'code',
        'type',
        'name',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function voteSummaries(): HasMany
    {
        return $this->hasMany(VoteSummary::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Get the prefix from code (P, A, J, etc.)
     */
    public function getTypePrefix(): string
    {
        return preg_replace('/[0-9]+/', '', $this->code);
    }

    /**
     * Check if this is a Professional division
     */
    public function isProfessional(): bool
    {
        return str_starts_with($this->code, 'P');
    }

    /**
     * Check if this is an Amateur division
     */
    public function isAmateur(): bool
    {
        return str_starts_with($this->code, 'A');
    }
}
