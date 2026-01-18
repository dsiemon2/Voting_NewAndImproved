<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_updates_email',
        'event_updates_sms',
        'event_updates_push',
        'voting_reminder_email',
        'voting_reminder_sms',
        'voting_reminder_push',
        'results_available_email',
        'results_available_sms',
        'results_available_push',
        'subscription_email',
        'subscription_sms',
        'subscription_push',
        'payment_email',
        'payment_sms',
        'payment_push',
        'security_email',
        'security_sms',
        'security_push',
    ];

    protected $casts = [
        'event_updates_email' => 'boolean',
        'event_updates_sms' => 'boolean',
        'event_updates_push' => 'boolean',
        'voting_reminder_email' => 'boolean',
        'voting_reminder_sms' => 'boolean',
        'voting_reminder_push' => 'boolean',
        'results_available_email' => 'boolean',
        'results_available_sms' => 'boolean',
        'results_available_push' => 'boolean',
        'subscription_email' => 'boolean',
        'subscription_sms' => 'boolean',
        'subscription_push' => 'boolean',
        'payment_email' => 'boolean',
        'payment_sms' => 'boolean',
        'payment_push' => 'boolean',
        'security_email' => 'boolean',
        'security_sms' => 'boolean',
        'security_push' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
