<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Braintree\Gateway;
use Braintree\Exception as BraintreeException;
use Exception;

/**
 * Braintree Payment Gateway Implementation
 *
 * Supports: Credit/Debit Cards, PayPal, Venmo, Apple Pay, Google Pay
 * Documentation: https://developer.paypal.com/braintree/docs
 */
class BraintreeGateway implements PaymentGatewayInterface
{
    protected array $config = [];
    protected ?Gateway $gateway = null;
    protected bool $initialized = false;

    public function getIdentifier(): string
    {
        return 'braintree';
    }

    public function getName(): string
    {
        return 'Braintree (PayPal)';
    }

    public function initialize(array $config): void
    {
        $this->config = $config;

        if (!empty($config['merchant_id']) && !empty($config['public_key']) && !empty($config['private_key'])) {
            $this->gateway = new Gateway([
                'environment' => ($config['sandbox'] ?? false) ? 'sandbox' : 'production',
                'merchantId' => $config['merchant_id'],
                'publicKey' => $config['public_key'],
                'privateKey' => $config['private_key'],
            ]);
            $this->initialized = true;
        }
    }

    public function isConfigured(): bool
    {
        return $this->initialized && $this->gateway !== null;
    }

    public function isTestMode(): bool
    {
        return $this->config['sandbox'] ?? false;
    }

    public function getPublishableKey(): ?string
    {
        return $this->config['public_key'] ?? null;
    }

    /**
     * Generate a client token for frontend SDK
     */
    public function generateClientToken(array $options = []): ?string
    {
        if (!$this->gateway) {
            return null;
        }

        try {
            $params = [];
            if (!empty($options['customer_id'])) {
                $params['customerId'] = $options['customer_id'];
            }

            return $this->gateway->clientToken()->generate($params);
        } catch (Exception $e) {
            return null;
        }
    }

    public function createPayment(float $amount, string $currency = 'usd', array $options = []): array
    {
        if (!$this->gateway) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $params = [
                'amount' => number_format($amount, 2, '.', ''),
                'options' => [
                    'submitForSettlement' => $options['capture'] ?? true,
                ],
            ];

            // Payment method nonce from frontend
            if (!empty($options['payment_method_nonce'])) {
                $params['paymentMethodNonce'] = $options['payment_method_nonce'];
            }

            if (!empty($options['customer_id'])) {
                $params['customerId'] = $options['customer_id'];
            }

            if (!empty($options['order_id'])) {
                $params['orderId'] = $options['order_id'];
            }

            // Custom fields as metadata
            if (!empty($options['metadata'])) {
                $params['customFields'] = $options['metadata'];
            }

            $result = $this->gateway->transaction()->sale($params);

            if ($result->success) {
                return [
                    'success' => true,
                    'id' => $result->transaction->id,
                    'status' => $this->mapStatus($result->transaction->status),
                    'amount' => (float) $result->transaction->amount,
                    'currency' => strtolower($result->transaction->currencyIsoCode ?? $currency),
                    'gateway' => $this->getIdentifier(),
                    'raw' => (array) $result->transaction,
                ];
            }

            return [
                'success' => false,
                'error' => $result->message,
                'gateway' => $this->getIdentifier(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function retrievePayment(string $paymentId): array
    {
        if (!$this->gateway) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $transaction = $this->gateway->transaction()->find($paymentId);

            return [
                'success' => true,
                'id' => $transaction->id,
                'status' => $this->mapStatus($transaction->status),
                'amount' => (float) $transaction->amount,
                'currency' => strtolower($transaction->currencyIsoCode ?? 'usd'),
                'gateway' => $this->getIdentifier(),
                'raw' => (array) $transaction,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function confirmPayment(string $paymentId, array $options = []): array
    {
        if (!$this->gateway) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            // Submit for settlement (capture)
            $result = $this->gateway->transaction()->submitForSettlement($paymentId);

            if ($result->success) {
                return [
                    'success' => true,
                    'id' => $result->transaction->id,
                    'status' => $this->mapStatus($result->transaction->status),
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => $result->message,
                'gateway' => $this->getIdentifier(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function cancelPayment(string $paymentId): array
    {
        if (!$this->gateway) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $result = $this->gateway->transaction()->void($paymentId);

            if ($result->success) {
                return [
                    'success' => true,
                    'id' => $result->transaction->id,
                    'status' => $this->mapStatus($result->transaction->status),
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => $result->message,
                'gateway' => $this->getIdentifier(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reason = null): array
    {
        if (!$this->gateway) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $params = $amount !== null ? number_format($amount, 2, '.', '') : null;
            $result = $this->gateway->transaction()->refund($paymentId, $params);

            if ($result->success) {
                return [
                    'success' => true,
                    'id' => $result->transaction->id,
                    'payment_id' => $paymentId,
                    'amount' => (float) $result->transaction->amount,
                    'status' => 'refunded',
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => $result->message,
                'gateway' => $this->getIdentifier(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function createCustomer(array $customerData): array
    {
        if (!$this->gateway) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $params = [];

            if (!empty($customerData['email'])) {
                $params['email'] = $customerData['email'];
            }
            if (!empty($customerData['first_name'])) {
                $params['firstName'] = $customerData['first_name'];
            }
            if (!empty($customerData['last_name'])) {
                $params['lastName'] = $customerData['last_name'];
            }
            if (!empty($customerData['phone'])) {
                $params['phone'] = $customerData['phone'];
            }

            $result = $this->gateway->customer()->create($params);

            if ($result->success) {
                return [
                    'success' => true,
                    'id' => $result->customer->id,
                    'email' => $result->customer->email,
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => $result->message,
                'gateway' => $this->getIdentifier(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function getSupportedMethods(): array
    {
        return ['card', 'paypal', 'venmo', 'apple_pay', 'google_pay'];
    }

    public function verifyWebhook(string $payload, string $signature): array
    {
        if (!$this->gateway) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            // Braintree webhook verification
            $notification = $this->gateway->webhookNotification()->parse($signature, $payload);

            return [
                'success' => true,
                'type' => $notification->kind,
                'data' => (array) $notification,
                'gateway' => $this->getIdentifier(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function mapStatus(string $gatewayStatus): string
    {
        return match ($gatewayStatus) {
            'authorized', 'authorizing' => 'authorized',
            'submitted_for_settlement', 'settling' => 'processing',
            'settled' => 'succeeded',
            'voided' => 'canceled',
            'failed', 'gateway_rejected', 'processor_declined' => 'failed',
            default => 'pending',
        };
    }

    public function getFrontendConfig(): array
    {
        return [
            'gateway' => $this->getIdentifier(),
            'client_token' => $this->generateClientToken(),
            'supported_methods' => $this->getSupportedMethods(),
            'test_mode' => $this->isTestMode(),
        ];
    }
}
