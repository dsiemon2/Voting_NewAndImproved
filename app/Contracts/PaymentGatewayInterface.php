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
}
