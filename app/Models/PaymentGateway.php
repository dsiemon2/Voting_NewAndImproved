<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'provider',
        'is_enabled',
        'publishable_key',
        'secret_key',
        'test_mode',
        'ach_enabled',
        'webhook_secret',
        'merchant_id',
        'additional_config',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'test_mode' => 'boolean',
        'ach_enabled' => 'boolean',
        'additional_config' => 'array',
    ];

    protected $hidden = [
        'secret_key',
        'webhook_secret',
    ];

    public static function getProviders(): array
    {
        return ['stripe', 'paypal', 'braintree', 'square', 'authorizenet'];
    }

    public static function getActiveProvider(): ?self
    {
        return self::where('is_enabled', true)->first();
    }
}
