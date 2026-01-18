<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentGatewayController extends Controller
{
    /**
     * Show the payment processing configuration page
     */
    public function index()
    {
        $gateways = PaymentGateway::orderBy('provider')->get()->keyBy('provider');

        // Default gateway configurations
        $providers = [
            'stripe' => [
                'name' => 'Stripe',
                'description' => 'Accept credit cards, debit cards, and ACH bank transfers',
                'icon' => 'fab fa-stripe',
                'color' => '#635BFF',
                'fee' => '2.9% + 30¢',
                'features' => ['Credit Cards', 'Debit Cards', 'ACH Bank Transfers', 'Apple Pay', 'Google Pay'],
            ],
            'paypal' => [
                'name' => 'PayPal',
                'description' => 'Accept PayPal payments, Pay Later, and Venmo',
                'icon' => 'fab fa-paypal',
                'color' => '#003087',
                'fee' => '3.49% + 49¢',
                'features' => ['PayPal Checkout', 'Pay Later', 'Venmo', 'Credit Cards', 'Debit Cards'],
            ],
            'braintree' => [
                'name' => 'Braintree',
                'description' => 'PayPal-owned payment processor with broad payment method support',
                'icon' => 'fas fa-credit-card',
                'color' => '#003087',
                'fee' => '2.59% + 49¢',
                'features' => ['Credit Cards', 'PayPal', 'Venmo', 'Apple Pay', 'Google Pay'],
            ],
            'square' => [
                'name' => 'Square',
                'description' => 'Integrated payment processing with POS capabilities',
                'icon' => 'fas fa-square',
                'color' => '#006AFF',
                'fee' => '2.6% + 10¢',
                'features' => ['Credit Cards', 'Debit Cards', 'Apple Pay', 'Google Pay', 'Cash App Pay'],
            ],
            'authorize' => [
                'name' => 'Authorize.net',
                'description' => 'Visa-owned payment gateway with extensive integrations',
                'icon' => 'fas fa-university',
                'color' => '#1C3D6E',
                'fee' => '2.9% + 30¢',
                'features' => ['Credit Cards', 'eChecks', 'Digital Payments', 'Fraud Detection'],
            ],
        ];

        return view('admin.payment-processing.index', [
            'gateways' => $gateways,
            'providers' => $providers,
        ]);
    }

    /**
     * Update or create gateway configuration
     */
    public function update(Request $request, string $provider)
    {
        $validator = Validator::make($request->all(), [
            'publishable_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
            'merchant_id' => 'nullable|string',
            'test_mode' => 'nullable|boolean',
            'ach_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $gateway = PaymentGateway::firstOrNew(['provider' => $provider]);

        // Update fields
        if ($request->filled('publishable_key')) {
            $gateway->publishable_key = $request->input('publishable_key');
        }

        // Only update secret key if a new one is provided
        if ($request->filled('secret_key')) {
            $gateway->secret_key = $request->input('secret_key');
        }

        if ($request->filled('webhook_secret')) {
            $gateway->webhook_secret = $request->input('webhook_secret');
        }

        if ($request->filled('merchant_id')) {
            $gateway->merchant_id = $request->input('merchant_id');
        }

        if ($request->has('test_mode')) {
            $gateway->test_mode = $request->boolean('test_mode');
        }

        if ($request->has('ach_enabled')) {
            $gateway->ach_enabled = $request->boolean('ach_enabled');
        }

        $gateway->save();

        return response()->json([
            'success' => true,
            'message' => ucfirst($provider) . ' configuration saved successfully',
            'gateway' => $this->maskGateway($gateway),
        ]);
    }

    /**
     * Enable a gateway (disables all others)
     */
    public function enable(Request $request, string $provider)
    {
        $gateway = PaymentGateway::where('provider', $provider)->first();

        if (!$gateway || !$gateway->publishable_key) {
            return response()->json([
                'success' => false,
                'message' => 'Please configure the API keys first',
            ], 422);
        }

        // Disable all other gateways
        PaymentGateway::where('provider', '!=', $provider)->update(['is_enabled' => false]);

        // Enable this gateway
        $gateway->is_enabled = true;
        $gateway->save();

        return response()->json([
            'success' => true,
            'message' => ucfirst($provider) . ' is now the active payment provider',
        ]);
    }

    /**
     * Disable a gateway
     */
    public function disable(Request $request, string $provider)
    {
        $gateway = PaymentGateway::where('provider', $provider)->first();

        if ($gateway) {
            $gateway->is_enabled = false;
            $gateway->save();
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($provider) . ' has been disabled',
        ]);
    }

    /**
     * Get all gateways as JSON
     */
    public function getGateways()
    {
        $gateways = PaymentGateway::all()->map(function ($gateway) {
            return $this->maskGateway($gateway);
        });

        return response()->json($gateways);
    }

    /**
     * Test gateway connection
     */
    public function testConnection(string $provider)
    {
        $gateway = PaymentGateway::where('provider', $provider)->first();

        if (!$gateway || !$gateway->secret_key) {
            return response()->json([
                'success' => false,
                'message' => 'No API keys configured for ' . ucfirst($provider),
            ]);
        }

        try {
            $result = $this->testProviderConnection($gateway);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Test connection based on provider type
     */
    protected function testProviderConnection(PaymentGateway $gateway): array
    {
        switch ($gateway->provider) {
            case 'stripe':
                return $this->testStripe($gateway);
            case 'braintree':
                return $this->testBraintree($gateway);
            case 'square':
                return $this->testSquare($gateway);
            case 'authorize':
                return $this->testAuthorize($gateway);
            default:
                return ['success' => false, 'message' => 'Unknown provider'];
        }
    }

    protected function testStripe(PaymentGateway $gateway): array
    {
        try {
            $stripe = new \Stripe\StripeClient($gateway->secret_key);
            $account = $stripe->accounts->retrieve();

            return [
                'success' => true,
                'message' => 'Connected to Stripe account: ' . ($account->business_profile->name ?? $account->id),
            ];
        } catch (\Stripe\Exception\AuthenticationException $e) {
            return ['success' => false, 'message' => 'Invalid API key'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function testBraintree(PaymentGateway $gateway): array
    {
        // Braintree test would require the SDK
        return [
            'success' => true,
            'message' => 'Braintree credentials saved (SDK not installed for live testing)',
        ];
    }

    protected function testSquare(PaymentGateway $gateway): array
    {
        // Square test would require the SDK
        return [
            'success' => true,
            'message' => 'Square credentials saved (SDK not installed for live testing)',
        ];
    }

    protected function testAuthorize(PaymentGateway $gateway): array
    {
        // Authorize.net test would require the SDK
        return [
            'success' => true,
            'message' => 'Authorize.net credentials saved (SDK not installed for live testing)',
        ];
    }

    /**
     * Mask sensitive gateway data for API responses
     */
    protected function maskGateway(PaymentGateway $gateway): array
    {
        return [
            'id' => $gateway->id,
            'provider' => $gateway->provider,
            'is_enabled' => $gateway->is_enabled,
            'publishable_key' => $gateway->publishable_key,
            'has_secret_key' => !empty($gateway->secret_key),
            'masked_secret_key' => $this->maskKey($gateway->secret_key),
            'test_mode' => $gateway->test_mode,
            'ach_enabled' => $gateway->ach_enabled,
            'has_webhook_secret' => !empty($gateway->webhook_secret),
            'merchant_id' => $gateway->merchant_id,
        ];
    }

    /**
     * Mask a key for display
     */
    protected function maskKey(?string $key): ?string
    {
        if (!$key) {
            return null;
        }

        $length = strlen($key);
        if ($length <= 8) {
            return str_repeat('•', $length);
        }

        return substr($key, 0, 4) . str_repeat('•', $length - 8) . substr($key, -4);
    }
}
