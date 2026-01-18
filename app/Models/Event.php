<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_template_id',
        'voting_type_id',
        'name',
        'description',
        'event_date',
        'voting_starts_at',
        'voting_ends_at',
        'auto_publish_results',
        'location',
        'state_id',
        'is_active',
        'is_public',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'auto_publish_results' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'settings' => 'array',
    ];

    // Relationships
    public function template(): BelongsTo
    {
        return $this->belongsTo(EventTemplate::class, 'event_template_id');
    }

    public function votingType(): BelongsTo
    {
        return $this->belongsTo(VotingType::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function votingConfig(): HasOne
    {
        return $this->hasOne(EventVotingConfig::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'event_modules')
            ->withPivot(['is_enabled', 'custom_label', 'configuration'])
            ->orderBy('modules.display_order');
    }

    public function moduleOverrides(): HasMany
    {
        return $this->hasMany(EventModule::class);
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class)->orderBy('display_order');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('display_order');
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

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function judges(): HasMany
    {
        return $this->hasMany(EventJudge::class);
    }

    /**
     * Check if user is a judge for this event
     */
    public function isJudge(User $user): bool
    {
        return $this->judges()->where('user_id', $user->id)->where('is_active', true)->exists();
    }

    /**
     * Get judge record for a user
     */
    public function getJudge(User $user): ?EventJudge
    {
        return $this->judges()->where('user_id', $user->id)->first();
    }

    // Accessors
    public function getParticipantLabelAttribute(): string
    {
        return $this->template?->participant_label ?? 'Participant';
    }

    public function getEntryLabelAttribute(): string
    {
        return $this->template?->entry_label ?? 'Entry';
    }

    // Module helpers
    public function hasModule(string $moduleCode): bool
    {
        $override = $this->moduleOverrides()->whereHas('module', fn($q) => $q->where('code', $moduleCode))->first();

        if ($override) {
            return $override->is_enabled;
        }

        return $this->template?->hasModule($moduleCode) ?? false;
    }

    public function getEnabledModules(): Collection
    {
        // Get template modules
        $templateModules = $this->template->modules()
            ->wherePivot('is_enabled_by_default', true)
            ->get()
            ->keyBy('id');

        // Apply overrides
        $overrides = $this->moduleOverrides()->with('module')->get()->keyBy('module_id');

        return $templateModules->filter(function ($module) use ($overrides) {
            $override = $overrides->get($module->id);
            return $override?->is_enabled ?? true;
        });
    }

    public function getModuleLabel(string $moduleCode): string
    {
        $override = $this->moduleOverrides()->whereHas('module', fn($q) => $q->where('code', $moduleCode))->first();

        if ($override?->custom_label) {
            return $override->custom_label;
        }

        return $this->template?->getModuleLabel($moduleCode) ?? ucfirst($moduleCode);
    }

    // Voting scheduling helpers
    public function isVotingOpen(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check scheduled voting times
        $now = now();

        if ($this->voting_starts_at && $now->lt($this->voting_starts_at)) {
            return false;
        }

        if ($this->voting_ends_at && $now->gt($this->voting_ends_at)) {
            return false;
        }

        $config = $this->votingConfig;
        if (!$config) {
            return true;
        }

        return $config->isVotingOpen();
    }

    /**
     * Check if voting is scheduled (has start/end times).
     */
    public function hasVotingSchedule(): bool
    {
        return $this->voting_starts_at || $this->voting_ends_at;
    }

    /**
     * Get voting status.
     */
    public function getVotingStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = now();

        if ($this->voting_starts_at && $now->lt($this->voting_starts_at)) {
            return 'scheduled';
        }

        if ($this->voting_ends_at && $now->gt($this->voting_ends_at)) {
            return 'ended';
        }

        return 'open';
    }

    /**
     * Duplicate this event with all its data.
     */
    public function duplicate(string $newName = null, bool $includeDivisions = true, bool $includeEntries = false, bool $includeParticipants = false): self
    {
        // Create new event
        $newEvent = $this->replicate([
            'created_at',
            'updated_at',
            'deleted_at',
            'voting_starts_at',
            'voting_ends_at',
        ]);

        $newEvent->name = $newName ?? $this->name . ' (Copy)';
        $newEvent->is_active = false; // Start as inactive
        $newEvent->created_by = auth()->id();
        $newEvent->save();

        // Copy voting config if exists
        if ($this->votingConfig) {
            $newEvent->votingConfig()->create(
                $this->votingConfig->replicate(['id', 'event_id'])->toArray()
            );
        }

        // Copy module overrides
        foreach ($this->moduleOverrides as $override) {
            $newEvent->moduleOverrides()->create(
                $override->replicate(['id', 'event_id'])->toArray()
            );
        }

        // Copy divisions
        if ($includeDivisions) {
            $divisionMap = [];
            foreach ($this->divisions as $division) {
                $newDivision = $division->replicate(['id', 'event_id']);
                $newDivision->event_id = $newEvent->id;
                $newDivision->save();
                $divisionMap[$division->id] = $newDivision->id;
            }

            // Copy participants if requested
            $participantMap = [];
            if ($includeParticipants) {
                foreach ($this->participants as $participant) {
                    $newParticipant = $participant->replicate(['id', 'event_id']);
                    $newParticipant->event_id = $newEvent->id;
                    $newParticipant->save();
                    $participantMap[$participant->id] = $newParticipant->id;
                }
            }

            // Copy entries if requested
            if ($includeEntries) {
                foreach ($this->entries as $entry) {
                    $newEntry = $entry->replicate(['id', 'event_id', 'division_id', 'participant_id']);
                    $newEntry->event_id = $newEvent->id;
                    $newEntry->division_id = $divisionMap[$entry->division_id] ?? null;
                    $newEntry->participant_id = $participantMap[$entry->participant_id] ?? null;
                    $newEntry->save();
                }
            }
        }

        // Copy categories
        foreach ($this->categories as $category) {
            $newCategory = $category->replicate(['id', 'event_id']);
            $newCategory->event_id = $newEvent->id;
            $newCategory->save();
        }

        // Dispatch webhook
        \App\Models\Webhook::dispatch('event.created', [
            'event_id' => $newEvent->id,
            'name' => $newEvent->name,
            'duplicated_from' => $this->id,
        ]);

        return $newEvent;
    }

    public function hasDivisions(): bool
    {
        return $this->divisions()->where('is_active', true)->exists();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString());
    }
}
