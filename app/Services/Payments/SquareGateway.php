<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Square\SquareClient;
use Square\Environment;
use Square\Models\CreatePaymentRequest;
use Square\Models\Money;
use Square\Models\RefundPaymentRequest;
use Square\Models\CreateCustomerRequest;
use Exception;

/**
 * Square Payment Gateway Implementation
 *
 * Supports: Credit/Debit Cards, Square Wallet, Cash App Pay, Afterpay
 * Documentation: https://developer.squareup.com/docs/payments-api/overview
 */
class SquareGateway implements PaymentGatewayInterface
{
    protected array $config = [];
    protected ?SquareClient $client = null;
    protected bool $initialized = false;

    public function getIdentifier(): string
    {
        return 'square';
    }

    public function getName(): string
    {
        return 'Square';
    }

    public function initialize(array $config): void
    {
        $this->config = $config;

        if (!empty($config['access_token'])) {
            $environment = ($config['sandbox'] ?? false)
                ? Environment::SANDBOX
                : Environment::PRODUCTION;

            $this->client = new SquareClient([
                'accessToken' => $config['access_token'],
                'environment' => $environment,
            ]);
            $this->initialized = true;
        }
    }

    public function isConfigured(): bool
    {
        return $this->initialized
            && !empty($this->config['access_token'])
            && !empty($this->config['application_id'])
            && !empty($this->config['location_id']);
    }

    public function isTestMode(): bool
    {
        return $this->config['sandbox'] ?? false;
    }

    public function getPublishableKey(): ?string
    {
        return $this->config['application_id'] ?? null;
    }

    public function getLocationId(): ?string
    {
        return $this->config['location_id'] ?? null;
    }

    public function createPayment(float $amount, string $currency = 'usd', array $options = []): array
    {
        if (!$this->client) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $money = new Money();
            $money->setAmount($this->convertToCents($amount));
            $money->setCurrency(strtoupper($currency));

            $idempotencyKey = $options['idempotency_key'] ?? uniqid('sq_', true);

            $request = new CreatePaymentRequest($options['source_id'] ?? '', $idempotencyKey);
            $request->setAmountMoney($money);
            $request->setLocationId($this->config['location_id']);

            if (!empty($options['customer_id'])) {
                $request->setCustomerId($options['customer_id']);
            }

            if (!empty($options['order_id'])) {
                $request->setReferenceId($options['order_id']);
            }

            if (!empty($options['note'])) {
                $request->setNote($options['note']);
            }

            // Auto-complete by default
            $request->setAutocomplete($options['autocomplete'] ?? true);

            $response = $this->client->getPaymentsApi()->createPayment($request);

            if ($response->isSuccess()) {
                $payment = $response->getResult()->getPayment();

                return [
                    'success' => true,
                    'id' => $payment->getId(),
                    'status' => $this->mapStatus($payment->getStatus()),
                    'amount' => $amount,
                    'currency' => $currency,
                    'gateway' => $this->getIdentifier(),
                    'raw' => json_decode(json_encode($payment), true),
                ];
            }

            $errors = $response->getErrors();
            $errorMsg = $errors[0]->getDetail() ?? 'Payment failed';

            return [
                'success' => false,
                'error' => $errorMsg,
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
        if (!$this->client) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $response = $this->client->getPaymentsApi()->getPayment($paymentId);

            if ($response->isSuccess()) {
                $payment = $response->getResult()->getPayment();
                $amountMoney = $payment->getAmountMoney();

                return [
                    'success' => true,
                    'id' => $payment->getId(),
                    'status' => $this->mapStatus($payment->getStatus()),
                    'amount' => $amountMoney ? $amountMoney->getAmount() / 100 : 0,
                    'currency' => strtolower($amountMoney ? $amountMoney->getCurrency() : 'usd'),
                    'gateway' => $this->getIdentifier(),
                    'raw' => json_decode(json_encode($payment), true),
                ];
            }

            return [
                'success' => false,
                'error' => 'Payment not found',
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

    public function confirmPayment(string $paymentId, array $options = []): array
    {
        if (!$this->client) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $response = $this->client->getPaymentsApi()->completePayment($paymentId, []);

            if ($response->isSuccess()) {
                $payment = $response->getResult()->getPayment();

                return [
                    'success' => true,
                    'id' => $payment->getId(),
                    'status' => $this->mapStatus($payment->getStatus()),
                    'gateway' => $this->getIdentifier(),
                ];
            }

            $errors = $response->getErrors();
            return [
                'success' => false,
                'error' => $errors[0]->getDetail() ?? 'Failed to complete payment',
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
        if (!$this->client) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $response = $this->client->getPaymentsApi()->cancelPayment($paymentId);

            if ($response->isSuccess()) {
                $payment = $response->getResult()->getPayment();

                return [
                    'success' => true,
                    'id' => $payment->getId(),
                    'status' => $this->mapStatus($payment->getStatus()),
                    'gateway' => $this->getIdentifier(),
                ];
            }

            $errors = $response->getErrors();
            return [
                'success' => false,
                'error' => $errors[0]->getDetail() ?? 'Failed to cancel payment',
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
        if (!$this->client) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $idempotencyKey = uniqid('refund_', true);

            $request = new RefundPaymentRequest($idempotencyKey);
            $request->setPaymentId($paymentId);

            if ($amount !== null) {
                $money = new Money();
                $money->setAmount($this->convertToCents($amount));
                $money->setCurrency('USD');
                $request->setAmountMoney($money);
            }

            if ($reason !== null) {
                $request->setReason($reason);
            }

            $response = $this->client->getRefundsApi()->refundPayment($request);

            if ($response->isSuccess()) {
                $refund = $response->getResult()->getRefund();

                return [
                    'success' => true,
                    'id' => $refund->getId(),
                    'payment_id' => $paymentId,
                    'amount' => $refund->getAmountMoney()->getAmount() / 100,
                    'status' => 'refunded',
                    'gateway' => $this->getIdentifier(),
                ];
            }

            $errors = $response->getErrors();
            return [
                'success' => false,
                'error' => $errors[0]->getDetail() ?? 'Refund failed',
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
        if (!$this->client) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $request = new CreateCustomerRequest();

            if (!empty($customerData['email'])) {
                $request->setEmailAddress($customerData['email']);
            }
            if (!empty($customerData['first_name'])) {
                $request->setGivenName($customerData['first_name']);
            }
            if (!empty($customerData['last_name'])) {
                $request->setFamilyName($customerData['last_name']);
            }
            if (!empty($customerData['phone'])) {
                $request->setPhoneNumber($customerData['phone']);
            }

            $response = $this->client->getCustomersApi()->createCustomer($request);

            if ($response->isSuccess()) {
                $customer = $response->getResult()->getCustomer();

                return [
                    'success' => true,
                    'id' => $customer->getId(),
                    'email' => $customer->getEmailAddress(),
                    'gateway' => $this->getIdentifier(),
                ];
            }

            $errors = $response->getErrors();
            return [
                'success' => false,
                'error' => $errors[0]->getDetail() ?? 'Failed to create customer',
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
        return ['card', 'square_wallet', 'cash_app_pay', 'afterpay'];
    }

    public function verifyWebhook(string $payload, string $signature): array
    {
        // Square webhook verification
        try {
            $signatureKey = $this->config['webhook_signature_key'] ?? '';

            if (!empty($signatureKey)) {
                $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $signatureKey, true));

                if (!hash_equals($expectedSignature, $signature)) {
                    return [
                        'success' => false,
                        'error' => 'Invalid signature',
                        'gateway' => $this->getIdentifier(),
                    ];
                }
            }

            $data = json_decode($payload, true);

            return [
                'success' => true,
                'type' => $data['type'] ?? '',
                'data' => $data['data'] ?? [],
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
        return match (strtoupper($gatewayStatus)) {
            'APPROVED', 'PENDING' => 'pending',
            'COMPLETED' => 'succeeded',
            'CANCELED' => 'canceled',
            'FAILED' => 'failed',
            default => 'pending',
        };
    }

    public function getFrontendConfig(): array
    {
        return [
            'gateway' => $this->getIdentifier(),
            'application_id' => $this->getPublishableKey(),
            'location_id' => $this->getLocationId(),
            'supported_methods' => $this->getSupportedMethods(),
            'test_mode' => $this->isTestMode(),
            'web_sdk_url' => $this->isTestMode()
                ? 'https://sandbox.web.squarecdn.com/v1/square.js'
                : 'https://web.squarecdn.com/v1/square.js',
        ];
    }

    protected function convertToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * List available locations
     */
    public function listLocations(): array
    {
        if (!$this->client) {
            return ['success' => false, 'error' => 'Gateway not initialized'];
        }

        try {
            $response = $this->client->getLocationsApi()->listLocations();

            if ($response->isSuccess()) {
                $locations = [];
                foreach ($response->getResult()->getLocations() ?? [] as $location) {
                    $locations[] = [
                        'id' => $location->getId(),
                        'name' => $location->getName(),
                        'status' => $location->getStatus(),
                    ];
                }

                return [
                    'success' => true,
                    'locations' => $locations,
                ];
            }

            return ['success' => false, 'error' => 'Failed to list locations'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
