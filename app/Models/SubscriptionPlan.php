<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'billing_period',
        'stripe_price_id',
        'max_events',
        'max_entries_per_event',
        'features',
        'has_basic_voting',
        'has_all_voting_types',
        'has_realtime_results',
        'has_custom_templates',
        'has_pdf_ballots',
        'has_excel_import',
        'has_judging_panels',
        'has_advanced_analytics',
        'has_white_label',
        'has_api_access',
        'has_custom_integrations',
        'support_level',
        'display_order',
        'is_popular',
        'is_active',
        'cta_text',
        'cta_style',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'has_basic_voting' => 'boolean',
        'has_all_voting_types' => 'boolean',
        'has_realtime_results' => 'boolean',
        'has_custom_templates' => 'boolean',
        'has_pdf_ballots' => 'boolean',
        'has_excel_import' => 'boolean',
        'has_judging_panels' => 'boolean',
        'has_advanced_analytics' => 'boolean',
        'has_white_label' => 'boolean',
        'has_api_access' => 'boolean',
        'has_custom_integrations' => 'boolean',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public static function getFreePlan(): ?self
    {
        return self::where('code', 'free')->first();
    }

    public static function getActivePlans()
    {
        return self::where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    public function isUnlimitedEvents(): bool
    {
        return $this->max_events === -1;
    }

    public function isUnlimitedEntries(): bool
    {
        return $this->max_entries_per_event === -1;
    }

    public function getFormattedPrice(): string
    {
        if ($this->price == 0) {
            return 'Free';
        }
        return '$' . number_format($this->price, 2);
    }

    public function hasFeature(string $feature): bool
    {
        $featureField = 'has_' . $feature;
        return $this->$featureField ?? false;
    }
}
