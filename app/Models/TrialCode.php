<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'requester_first_name',
        'requester_last_name',
        'requester_email',
        'requester_phone',
        'requester_organization',
        'delivery_method',
        'user_id',
        'status',
        'sent_at',
        'redeemed_at',
        'expires_at',
        'extension_count',
        'extended_by',
        'last_extended_at',
        'parent_code_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_extended_at' => 'datetime',
        'extension_count' => 'integer',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_REDEEMED = 'redeemed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';

    /**
     * Maximum number of extensions allowed
     */
    const MAX_EXTENSIONS = 3;

    /**
     * Days until unredeemed code expires
     */
    const UNREDEEMED_EXPIRY_DAYS = 7;

    /**
     * Trial period in days after redemption
     */
    const TRIAL_PERIOD_DAYS = 14;

    /**
     * Get the user who redeemed this code
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who extended this code
     */
    public function extendedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'extended_by');
    }

    /**
     * Get the parent code (if this is an extension)
     */
    public function parentCode(): BelongsTo
    {
        return $this->belongsTo(TrialCode::class, 'parent_code_id');
    }

    /**
     * Get extension codes
     */
    public function extensionCodes()
    {
        return $this->hasMany(TrialCode::class, 'parent_code_id');
    }

    /**
     * Get full name of requester
     */
    public function getRequesterFullNameAttribute(): string
    {
        return trim($this->requester_first_name . ' ' . $this->requester_last_name);
    }

    /**
     * Check if code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast() || $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Check if code can be redeemed
     */
    public function canBeRedeemed(): bool
    {
        return $this->status === self::STATUS_SENT
            && !$this->isExpired()
            && $this->user_id === null;
    }

    /**
     * Check if code can be extended by admin
     */
    public function canBeExtended(): bool
    {
        return $this->status === self::STATUS_REDEEMED
            && !$this->isExpired()
            && $this->extension_count < self::MAX_EXTENSIONS;
    }

    /**
     * Check if code can be revoked
     */
    public function canBeRevoked(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_SENT]);
    }

    /**
     * Get remaining extensions
     */
    public function getRemainingExtensionsAttribute(): int
    {
        return max(0, self::MAX_EXTENSIONS - $this->extension_count);
    }

    /**
     * Get days remaining in trial
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        return max(0, now()->diffInDays($this->expires_at, false));
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_SENT => 'info',
            self::STATUS_REDEEMED => 'success',
            self::STATUS_EXPIRED => 'secondary',
            self::STATUS_REVOKED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Scope: Active codes (not expired, not revoked)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_EXPIRED, self::STATUS_REVOKED])
            ->where('expires_at', '>', now());
    }

    /**
     * Scope: By email
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('requester_email', strtolower($email));
    }

    /**
     * Scope: By phone
     */
    public function scopeByPhone($query, string $phone)
    {
        return $query->where('requester_phone', $phone);
    }

    /**
     * Scope: Pending or sent (not yet redeemed)
     */
    public function scopePendingRedemption($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_SENT]);
    }

    /**
     * Check if email already has an active trial code
     */
    public static function emailHasActiveCode(string $email): bool
    {
        return self::byEmail($email)
            ->active()
            ->pendingRedemption()
            ->exists();
    }

    /**
     * Check if phone already has an active trial code
     */
    public static function phoneHasActiveCode(string $phone): bool
    {
        return self::byPhone($phone)
            ->active()
            ->pendingRedemption()
            ->exists();
    }

    /**
     * Find code by code string
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', strtoupper($code))->first();
    }
}
