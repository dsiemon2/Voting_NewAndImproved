<?php

namespace App\Contracts;

/**
 * Interface for Payment Gateway implementations
 *
 * All payment gateways (Stripe, Braintree, PayPal, Square, Authorize.net)
 * must implement this interface to ensure consistent behavior.
 */
interface PaymentGatewayInterface
{
    /**
     * Get the gateway identifier
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Get the gateway display name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Initialize the gateway with configuration
     *
     * @param array $config Configuration options
     * @return void
     */
    public function initialize(array $config): void;

    /**
     * Check if the gateway is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Check if the gateway is in test/sandbox mode
     *
     * @return bool
     */
    public function isTestMode(): bool;

    /**
     * Get the publishable/public key for frontend use
     *
     * @return string|null
     */
    public function getPublishableKey(): ?string;

    /**
     * Create a payment intent/transaction
     *
     * @param float $amount Amount in dollars
     * @param string $currency Currency code (e.g., 'usd')
     * @param array $options Additional options (metadata, customer info, etc.)
     * @return array Payment intent data with id, client_secret, status
     */
    public function createPayment(float $amount, string $currency = 'usd', array $options = []): array;

    /**
     * Retrieve a payment by ID
     *
     * @param string $paymentId The payment/transaction ID
     * @return array Payment data
     */
    public function retrievePayment(string $paymentId): array;

    /**
     * Confirm/capture a payment
     *
     * @param string $paymentId The payment/transaction ID
     * @param array $options Additional options (payment method, etc.)
     * @return array Confirmed payment data
     */
    public function confirmPayment(string $paymentId, array $options = []): array;

    /**
     * Cancel/void a payment
     *
     * @param string $paymentId The payment/transaction ID
     * @return array Cancelled payment data
     */
    public function cancelPayment(string $paymentId): array;

    /**
     * Refund a payment
     *
     * @param string $paymentId The payment/transaction ID
     * @param float|null $amount Amount to refund (null for full refund)
     * @param string|null $reason Reason for refund
     * @return array Refund data
     */
    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reason = null): array;

    /**
     * Create a customer record in the gateway
     *
     * @param array $customerData Customer data (email, name, etc.)
     * @return array Customer data with gateway customer ID
     */
    public function createCustomer(array $customerData): array;

    /**
     * Get supported payment methods
     *
     * @return array List of supported payment method types
     */
    public function getSupportedMethods(): array;

    /**
     * Verify a webhook signature
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Signature header
     * @return array Verified webhook event data
     */
    public function verifyWebhook(string $payload, string $signature): array;

    /**
     * Map gateway-specific status to standardized status
     *
     * @param string $gatewayStatus The gateway's native status
     * @return string Standardized status (pending, processing, succeeded, failed, canceled, refunded)
     */
    public function mapStatus(string $gatewayStatus): string;

    /**
     * Get frontend configuration for checkout
     *
     * @return array Configuration needed for frontend integration
     */
    public function getFrontendConfig(): array;

    /**
     * Create a subscription with optional trial period
     *
     * @param string $customerId Gateway customer ID
     * @param float $amount Monthly amount in dollars
     * @param array $options Options including:
     *   - trial_days: int Number of trial days (default 14)
     *   - interval: string 'month' or 'year'
     *   - metadata: array Additional metadata
     *   - plan_name: string Name of the plan
     *   - plan_description: string Description of the plan
     * @return array Subscription data with id, status, trial_end, etc.
     */
    public function createSubscription(string $customerId, float $amount, array $options = []): array;

    /**
     * Cancel a subscription
     *
     * @param string $subscriptionId The subscription ID
     * @param bool $cancelImmediately If false, cancels at period end
     * @return array Cancellation result
     */
    public function cancelSubscription(string $subscriptionId, bool $cancelImmediately = false): array;

    /**
     * Resume a canceled subscription (before period ends)
     *
     * @param string $subscriptionId The subscription ID
     * @return array Resume result
     */
    public function resumeSubscription(string $subscriptionId): array;

    /**
     * Retrieve subscription details
     *
     * @param string $subscriptionId The subscription ID
     * @return array Subscription data
     */
    public function retrieveSubscription(string $subscriptionId): array;

    /**
     * Check if gateway supports recurring/subscription payments
     *
     * @return bool
     */
    public function supportsSubscriptions(): bool;
}
