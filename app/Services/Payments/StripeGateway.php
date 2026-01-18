<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Customer;
use Stripe\Webhook;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Exception;

/**
 * Stripe Payment Gateway Implementation
 *
 * Supports: Credit/Debit Cards, Apple Pay, Google Pay, ACH Bank Transfers
 * Documentation: https://stripe.com/docs/api
 */
class StripeGateway implements PaymentGatewayInterface
{
    protected array $config = [];
    protected bool $initialized = false;

    public function getIdentifier(): string
    {
        return 'stripe';
    }

    public function getName(): string
    {
        return 'Stripe';
    }

    public function initialize(array $config): void
    {
        $this->config = $config;

        if (!empty($config['secret_key'])) {
            Stripe::setApiKey($config['secret_key']);
            $this->initialized = true;
        }
    }

    public function isConfigured(): bool
    {
        return $this->initialized
            && !empty($this->config['secret_key'])
            && !empty($this->config['publishable_key']);
    }

    public function isTestMode(): bool
    {
        return $this->config['test_mode'] ?? false;
    }

    public function getPublishableKey(): ?string
    {
        return $this->config['publishable_key'] ?? null;
    }

    public function createPayment(float $amount, string $currency = 'usd', array $options = []): array
    {
        try {
            $params = [
                'amount' => $this->convertToCents($amount),
                'currency' => strtolower($currency),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ];

            if (!empty($options['metadata'])) {
                $params['metadata'] = $options['metadata'];
            }

            if (!empty($options['customer_id'])) {
                $params['customer'] = $options['customer_id'];
            }

            if (!empty($options['description'])) {
                $params['description'] = $options['description'];
            }

            if (!empty($options['receipt_email'])) {
                $params['receipt_email'] = $options['receipt_email'];
            }

            // ACH/Bank transfer support
            if (!empty($options['payment_method_types'])) {
                unset($params['automatic_payment_methods']);
                $params['payment_method_types'] = $options['payment_method_types'];
            }

            $paymentIntent = PaymentIntent::create($params);

            return [
                'success' => true,
                'id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $this->mapStatus($paymentIntent->status),
                'amount' => $amount,
                'currency' => $currency,
                'gateway' => $this->getIdentifier(),
                'raw' => $paymentIntent->toArray(),
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getStripeCode(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function retrievePayment(string $paymentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentId);

            return [
                'success' => true,
                'id' => $paymentIntent->id,
                'status' => $this->mapStatus($paymentIntent->status),
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'gateway' => $this->getIdentifier(),
                'raw' => $paymentIntent->toArray(),
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function confirmPayment(string $paymentId, array $options = []): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentId);

            $confirmParams = [];
            if (!empty($options['payment_method'])) {
                $confirmParams['payment_method'] = $options['payment_method'];
            }

            $paymentIntent = $paymentIntent->confirm($confirmParams);

            return [
                'success' => true,
                'id' => $paymentIntent->id,
                'status' => $this->mapStatus($paymentIntent->status),
                'gateway' => $this->getIdentifier(),
                'raw' => $paymentIntent->toArray(),
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function cancelPayment(string $paymentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentId);
            $paymentIntent = $paymentIntent->cancel();

            return [
                'success' => true,
                'id' => $paymentIntent->id,
                'status' => $this->mapStatus($paymentIntent->status),
                'gateway' => $this->getIdentifier(),
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reason = null): array
    {
        try {
            $params = ['payment_intent' => $paymentId];

            if ($amount !== null) {
                $params['amount'] = $this->convertToCents($amount);
            }

            if ($reason !== null) {
                $params['reason'] = $reason;
            }

            $refund = Refund::create($params);

            return [
                'success' => true,
                'id' => $refund->id,
                'payment_id' => $paymentId,
                'amount' => $refund->amount / 100,
                'status' => $refund->status,
                'gateway' => $this->getIdentifier(),
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function createCustomer(array $customerData): array
    {
        try {
            $params = [];

            if (!empty($customerData['email'])) {
                $params['email'] = $customerData['email'];
            }
            if (!empty($customerData['name'])) {
                $params['name'] = $customerData['name'];
            }
            if (!empty($customerData['phone'])) {
                $params['phone'] = $customerData['phone'];
            }
            if (!empty($customerData['metadata'])) {
                $params['metadata'] = $customerData['metadata'];
            }

            $customer = Customer::create($params);

            return [
                'success' => true,
                'id' => $customer->id,
                'email' => $customer->email,
                'gateway' => $this->getIdentifier(),
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getIdentifier(),
            ];
        }
    }

    public function getSupportedMethods(): array
    {
        $methods = ['card', 'apple_pay', 'google_pay'];

        if ($this->config['ach_enabled'] ?? false) {
            $methods[] = 'us_bank_account';
        }

        return $methods;
    }

    public function verifyWebhook(string $payload, string $signature): array
    {
        try {
            $webhookSecret = $this->config['webhook_secret'] ?? '';

            if (empty($webhookSecret)) {
                throw new Exception('Webhook secret not configured');
            }

            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);

            return [
                'success' => true,
                'type' => $event->type,
                'data' => $event->data->object->toArray(),
                'gateway' => $this->getIdentifier(),
            ];
        } catch (SignatureVerificationException $e) {
            return [
                'success' => false,
                'error' => 'Invalid signature',
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
            'requires_payment_method', 'requires_confirmation', 'requires_action' => 'pending',
            'processing' => 'processing',
            'succeeded' => 'succeeded',
            'canceled' => 'canceled',
            'requires_capture' => 'authorized',
            default => 'failed',
        };
    }

    public function getFrontendConfig(): array
    {
        return [
            'gateway' => $this->getIdentifier(),
            'publishable_key' => $this->getPublishableKey(),
            'supported_methods' => $this->getSupportedMethods(),
            'test_mode' => $this->isTestMode(),
        ];
    }

    protected function convertToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
