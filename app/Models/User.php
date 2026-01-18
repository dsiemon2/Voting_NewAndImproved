<?php

namespace App\Models;

use App\Models\EventJudge;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relationships
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function voterClasses(): HasMany
    {
        return $this->hasMany(UserVoterClass::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(UserPaymentMethod::class);
    }

    public function notificationPreferences()
    {
        return $this->hasOne(UserNotificationPreference::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    // Authorization helpers
    public function isAdmin(): bool
    {
        return $this->role?->name === 'Administrator';
    }

    public function isMember(): bool
    {
        return in_array($this->role?->name, ['Administrator', 'Member']);
    }

    public function isJudge(): bool
    {
        return $this->role?->name === 'Judge';
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    public function getWeightForEvent(Event $event): float
    {
        // First check if user is a judge for this event
        $judge = $event->getJudge($this);
        if ($judge && $judge->is_active) {
            return (float) $judge->vote_weight;
        }

        // Otherwise use voter class weight
        $voterClass = $this->voterClasses()
            ->where('event_id', $event->id)
            ->first();

        return $voterClass?->voterWeightClass?->weight_multiplier ?? 1.0;
    }

    /**
     * Check if user is a judge for a specific event
     */
    public function isJudgeForEvent(Event $event): bool
    {
        return $event->isJudge($this);
    }

    /**
     * Get judge record for a specific event
     */
    public function getJudgeRecord(Event $event): ?EventJudge
    {
        return $event->getJudge($this);
    }

    // Subscription relationships and methods
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription(): ?UserSubscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->with('plan')
            ->first();
    }

    public function currentPlan(): ?SubscriptionPlan
    {
        $subscription = $this->activeSubscription();
        if ($subscription) {
            return $subscription->plan;
        }
        // Return free plan as default
        return SubscriptionPlan::getFreePlan();
    }

    public function isSubscribed(): bool
    {
        return $this->activeSubscription() !== null;
    }

    public function isOnFreePlan(): bool
    {
        $plan = $this->currentPlan();
        return !$plan || $plan->code === 'free';
    }

    public function isOnPaidPlan(): bool
    {
        return $this->isSubscribed() && !$this->isOnFreePlan();
    }

    public function hasFeature(string $feature): bool
    {
        $plan = $this->currentPlan();
        return $plan ? $plan->hasFeature($feature) : false;
    }

    public function canCreateEvent(): bool
    {
        $plan = $this->currentPlan();
        if (!$plan) {
            return true; // Allow if no plan system
        }

        if ($plan->isUnlimitedEvents()) {
            return true;
        }

        $activeEvents = $this->createdEvents()->where('is_active', true)->count();
        return $activeEvents < $plan->max_events;
    }

    public function canAddEntries(Event $event, int $count = 1): bool
    {
        $plan = $this->currentPlan();
        if (!$plan) {
            return true;
        }

        if ($plan->isUnlimitedEntries()) {
            return true;
        }

        $currentEntries = $event->entries()->count();
        return ($currentEntries + $count) <= $plan->max_entries_per_event;
    }

    public function getRemainingEvents(): int|string
    {
        $plan = $this->currentPlan();
        if (!$plan || $plan->isUnlimitedEvents()) {
            return 'Unlimited';
        }

        $activeEvents = $this->createdEvents()->where('is_active', true)->count();
        return max(0, $plan->max_events - $activeEvents);
    }

    public function getRemainingEntriesForEvent(Event $event): int|string
    {
        $plan = $this->currentPlan();
        if (!$plan || $plan->isUnlimitedEntries()) {
            return 'Unlimited';
        }

        $currentEntries = $event->entries()->count();
        return max(0, $plan->max_entries_per_event - $currentEntries);
    }
}
