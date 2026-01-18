<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'ended_at',
        'payment_method_brand',
        'payment_method_last4',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled' || $this->canceled_at !== null;
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    public function daysUntilRenewal(): ?int
    {
        if (!$this->current_period_end) {
            return null;
        }
        return now()->diffInDays($this->current_period_end, false);
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'active' => 'badge-success',
            'trialing' => 'badge-info',
            'canceled' => 'badge-warning',
            'past_due' => 'badge-danger',
            'paused' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }
}
