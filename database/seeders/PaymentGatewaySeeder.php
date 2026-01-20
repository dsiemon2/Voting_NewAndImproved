<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Stripe - Configure in .env file:
        //   STRIPE_PUBLISHABLE_KEY=pk_live_...
        //   STRIPE_SECRET_KEY=sk_live_...
        PaymentGateway::updateOrCreate(
            ['provider' => 'stripe'],
            [
                'is_enabled' => true,
                'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
                'secret_key' => env('STRIPE_SECRET_KEY', ''),
                'test_mode' => false,
                'ach_enabled' => false,
            ]
        );

        // PayPal - Configure in .env file:
        //   PAYPAL_CLIENT_ID=...
        //   PAYPAL_CLIENT_SECRET=...
        PaymentGateway::updateOrCreate(
            ['provider' => 'paypal'],
            [
                'is_enabled' => false,
                'publishable_key' => env('PAYPAL_CLIENT_ID', ''),
                'secret_key' => env('PAYPAL_CLIENT_SECRET', ''),
                'test_mode' => true,
            ]
        );

        // Braintree - Placeholder for future configuration
        PaymentGateway::updateOrCreate(
            ['provider' => 'braintree'],
            [
                'is_enabled' => false,
                'test_mode' => true,
            ]
        );

        // Square - Placeholder for future configuration
        PaymentGateway::updateOrCreate(
            ['provider' => 'square'],
            [
                'is_enabled' => false,
                'test_mode' => true,
            ]
        );

        // Authorize.net - Placeholder for future configuration
        PaymentGateway::updateOrCreate(
            ['provider' => 'authorize'],
            [
                'is_enabled' => false,
                'test_mode' => true,
            ]
        );
    }
}
