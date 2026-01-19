<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentGateway;
use Exception;

/**
 * Payment Manager - Factory for payment services
 * Manages payment provider selection and configuration
 */
class PaymentManager
{
    protected array $gateways = [];
    protected ?string $activeProvider = null;
    protected bool $initialized = false;

    public function __construct()
    {
        // Register all payment gateways
        $this->gateways = [
            'stripe' => new StripeGateway(),
            'paypal' => new PayPalGateway(),
            'braintree' => new BraintreeGateway(),
            'square' => new SquareGateway(),
            'authorizenet' => new AuthorizeNetGateway(),
        ];
    }

    /**
     * Initialize payment gateways from database configuration
     */
    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        try {
            $enabledGateways = PaymentGateway::where('is_enabled', true)->get();

            foreach ($enabledGateways as $gatewayConfig) {
                $provider = $gatewayConfig->provider;

                if (isset($this->gateways[$provider])) {
                    $config = [
                        'publishable_key' => $gatewayConfig->publishable_key,
                        'secret_key' => $gatewayConfig->secret_key,
                        'test_mode' => $gatewayConfig->test_mode,
                        'ach_enabled' => $gatewayConfig->ach_enabled,
                        'webhook_secret' => $gatewayConfig->webhook_secret,
                        'merchant_id' => $gatewayConfig->merchant_id,
                        // PayPal specific
                        'client_id' => $gatewayConfig->publishable_key,
                        'client_secret' => $gatewayConfig->secret_key,
                        'sandbox' => $gatewayConfig->test_mode,
                        // Braintree specific
                        'public_key' => $gatewayConfig->publishable_key,
                        'private_key' => $gatewayConfig->secret_key,
                        // Square specific
                        'application_id' => $gatewayConfig->publishable_key,
                        'access_token' => $gatewayConfig->secret_key,
                        'location_id' => $gatewayConfig->additional_config['location_id'] ?? null,
                        // Authorize.net specific
                        'login_id' => $gatewayConfig->publishable_key,
                        'transaction_key' => $gatewayConfig->secret_key,
                        'client_key' => $gatewayConfig->additional_config['client_key'] ?? null,
                        'signature_key' => $gatewayConfig->additional_config['signature_key'] ?? null,
                    ];

                    $this->gateways[$provider]->initialize($config);

                    // Set first enabled provider as active
                    if (!$this->activeProvider) {
                        $this->activeProvider = $provider;
                    }
                }
            }

            $this->initialized = true;
        } catch (Exception $e) {
            throw new Exception('Failed to initialize PaymentManager: ' . $e->getMessage());
        }
    }

    /**
     * Get the active payment provider
     */
    public function getActiveProvider(): ?string
    {
        return $this->activeProvider;
    }

    /**
     * Set the active payment provider
     */
    public function setActiveProvider(string $provider): void
    {
        if (!isset($this->gateways[$provider])) {
            throw new Exception("Unknown payment provider: {$provider}");
        }
        $this->activeProvider = $provider;
    }

    /**
     * Get a specific payment gateway service
     */
    public function getGateway(?string $provider = null): PaymentGatewayInterface
    {
        $this->ensureInitialized();

        $targetProvider = $provider ?? $this->activeProvider;

        if (!$targetProvider) {
            throw new Exception('No active payment provider configured');
        }

        if (!isset($this->gateways[$targetProvider])) {
            throw new Exception("Payment gateway not found: {$targetProvider}");
        }

        return $this->gateways[$targetProvider];
    }

    /**
     * Get all available providers
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->gateways);
    }

    /**
     * Create a payment using the active gateway
     */
    public function createPayment(float $amount, string $currency = 'usd', array $options = []): array
    {
        return $this->getGateway()->createPayment($amount, $currency, $options);
    }

    /**
     * Retrieve a payment by ID
     */
    public function retrievePayment(string $paymentId): array
    {
        return $this->getGateway()->retrievePayment($paymentId);
    }

    /**
     * Confirm a payment
     */
    public function confirmPayment(string $paymentId, array $options = []): array
    {
        return $this->getGateway()->confirmPayment($paymentId, $options);
    }

    /**
     * Cancel a payment
     */
    public function cancelPayment(string $paymentId): array
    {
        return $this->getGateway()->cancelPayment($paymentId);
    }

    /**
     * Refund a payment
     */
    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reason = null): array
    {
        return $this->getGateway()->refundPayment($paymentId, $amount, $reason);
    }

    /**
     * Get the publishable key for frontend use
     */
    public function getPublishableKey(): ?string
    {
        return $this->getGateway()->getPublishableKey();
    }

    /**
     * Check if in test mode
     */
    public function isTestMode(): bool
    {
        return $this->getGateway()->isTestMode();
    }

    /**
     * Get frontend configuration for checkout
     */
    public function getFrontendConfig(): array
    {
        return $this->getGateway()->getFrontendConfig();
    }

    /**
     * Verify a webhook signature
     */
    public function verifyWebhook(string $payload, string $signature): array
    {
        return $this->getGateway()->verifyWebhook($payload, $signature);
    }

    /**
     * Check if current gateway supports subscriptions
     */
    public function supportsSubscriptions(): bool
    {
        return $this->getGateway()->supportsSubscriptions();
    }

    /**
     * Create a subscription with optional trial period
     */
    public function createSubscription(string $customerId, float $amount, array $options = []): array
    {
        return $this->getGateway()->createSubscription($customerId, $amount, $options);
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId, bool $cancelImmediately = false): array
    {
        return $this->getGateway()->cancelSubscription($subscriptionId, $cancelImmediately);
    }

    /**
     * Resume a canceled subscription
     */
    public function resumeSubscription(string $subscriptionId): array
    {
        return $this->getGateway()->resumeSubscription($subscriptionId);
    }

    /**
     * Retrieve subscription details
     */
    public function retrieveSubscription(string $subscriptionId): array
    {
        return $this->getGateway()->retrieveSubscription($subscriptionId);
    }

    // Gateway-specific accessors

    public function getStripeGateway(): StripeGateway
    {
        return $this->gateways['stripe'];
    }

    public function getPayPalGateway(): PayPalGateway
    {
        return $this->gateways['paypal'];
    }

    public function getBraintreeGateway(): BraintreeGateway
    {
        return $this->gateways['braintree'];
    }

    public function getSquareGateway(): SquareGateway
    {
        return $this->gateways['square'];
    }

    public function getAuthorizeNetGateway(): AuthorizeNetGateway
    {
        return $this->gateways['authorizenet'];
    }

    protected function ensureInitialized(): void
    {
        if (!$this->initialized) {
            $this->initialize();
        }
    }
}
