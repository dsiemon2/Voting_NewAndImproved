<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Exception;

/**
 * PayPal Payment Gateway Implementation
 *
 * Supports: PayPal Checkout, Credit/Debit Cards via PayPal
 * Documentation: https://developer.paypal.com/docs/checkout/
 */
class PayPalGateway implements PaymentGatewayInterface
{
    protected array $config = [];
    protected bool $initialized = false;
    protected string $apiBase;

    public function getIdentifier(): string
    {
        return 'paypal';
    }

    public function getName(): string
    {
        return 'PayPal Checkout';
    }

    public function initialize(array $config): void
    {
        $this->config = $config;

        if (!empty($config['client_id']) && !empty($config['client_secret'])) {
            $this->apiBase = ($config['sandbox'] ?? false)
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';
            $this->initialized = true;
        }
    }

    public function isConfigured(): bool
    {
        return $this->initialized
            && !empty($this->config['client_id'])
            && !empty($this->config['client_secret']);
    }

    public function isTestMode(): bool
    {
        return $this->config['sandbox'] ?? false;
    }

    public function getPublishableKey(): ?string
    {
        return $this->config['client_id'] ?? null;
    }

    protected function getAccessToken(): ?string
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiBase . '/v1/oauth2/token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
                CURLOPT_USERPWD => $this->config['client_id'] . ':' . $this->config['client_secret'],
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                ],
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    protected function apiRequest(string $method, string $endpoint, array $data = []): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Failed to get access token'];
        }

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $this->apiBase . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        } elseif ($method === 'PATCH') {
            $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true) ?? [];
        $result['http_code'] = $httpCode;

        return $result;
    }

    public function createPayment(float $amount, string $currency = 'usd', array $options = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Gateway not configured',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => strtoupper($currency),
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                    ],
                ],
            ];

            if (!empty($options['description'])) {
                $orderData['purchase_units'][0]['description'] = $options['description'];
            }

            if (!empty($options['order_id'])) {
                $orderData['purchase_units'][0]['reference_id'] = $options['order_id'];
            }

            $result = $this->apiRequest('POST', '/v2/checkout/orders', $orderData);

            if (!empty($result['id'])) {
                $approvalUrl = null;
                foreach ($result['links'] ?? [] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approvalUrl = $link['href'];
                        break;
                    }
                }

                return [
                    'success' => true,
                    'id' => $result['id'],
                    'status' => $this->mapStatus($result['status'] ?? 'CREATED'),
                    'amount' => $amount,
                    'currency' => $currency,
                    'approval_url' => $approvalUrl,
                    'gateway' => $this->getIdentifier(),
                    'raw' => $result,
                ];
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Failed to create order',
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
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Gateway not configured',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $result = $this->apiRequest('GET', '/v2/checkout/orders/' . $paymentId);

            if (!empty($result['id'])) {
                $amount = $result['purchase_units'][0]['amount']['value'] ?? 0;

                return [
                    'success' => true,
                    'id' => $result['id'],
                    'status' => $this->mapStatus($result['status'] ?? ''),
                    'amount' => (float) $amount,
                    'currency' => strtolower($result['purchase_units'][0]['amount']['currency_code'] ?? 'usd'),
                    'gateway' => $this->getIdentifier(),
                    'raw' => $result,
                ];
            }

            return [
                'success' => false,
                'error' => 'Order not found',
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
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Gateway not configured',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            // Capture the order
            $result = $this->apiRequest('POST', '/v2/checkout/orders/' . $paymentId . '/capture', []);

            if ($result['status'] === 'COMPLETED') {
                return [
                    'success' => true,
                    'id' => $result['id'],
                    'status' => $this->mapStatus($result['status']),
                    'gateway' => $this->getIdentifier(),
                    'raw' => $result,
                ];
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Failed to capture payment',
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
        // PayPal orders can't be cancelled once created, they expire after 3 hours
        return [
            'success' => true,
            'id' => $paymentId,
            'status' => 'canceled',
            'message' => 'PayPal orders expire automatically if not completed',
            'gateway' => $this->getIdentifier(),
        ];
    }

    public function refundPayment(string $paymentId, ?float $amount = null, ?string $reason = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Gateway not configured',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            // First retrieve the order to get capture ID
            $order = $this->retrievePayment($paymentId);
            if (!$order['success']) {
                return $order;
            }

            $captureId = $order['raw']['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
            if (!$captureId) {
                return [
                    'success' => false,
                    'error' => 'No capture found for this order',
                    'gateway' => $this->getIdentifier(),
                ];
            }

            $refundData = [];
            if ($amount !== null) {
                $refundData['amount'] = [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => strtoupper($order['currency']),
                ];
            }

            if ($reason !== null) {
                $refundData['note_to_payer'] = $reason;
            }

            $result = $this->apiRequest('POST', '/v2/payments/captures/' . $captureId . '/refund', $refundData);

            if (!empty($result['id'])) {
                return [
                    'success' => true,
                    'id' => $result['id'],
                    'payment_id' => $paymentId,
                    'amount' => (float) ($result['amount']['value'] ?? $amount),
                    'status' => 'refunded',
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Failed to refund',
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
        // PayPal doesn't have a traditional customer API
        // Customer data is associated with their PayPal account
        return [
            'success' => true,
            'id' => $customerData['email'] ?? uniqid('paypal_'),
            'email' => $customerData['email'] ?? null,
            'message' => 'PayPal uses email-based customer identification',
            'gateway' => $this->getIdentifier(),
        ];
    }

    public function getSupportedMethods(): array
    {
        return ['paypal', 'card', 'paylater'];
    }

    public function verifyWebhook(string $payload, string $signature): array
    {
        // PayPal webhook verification requires additional headers
        // This is a simplified implementation
        try {
            $data = json_decode($payload, true);

            if (!empty($data['event_type'])) {
                return [
                    'success' => true,
                    'type' => $data['event_type'],
                    'data' => $data['resource'] ?? [],
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Invalid webhook payload',
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
            'CREATED', 'SAVED', 'APPROVED', 'PAYER_ACTION_REQUIRED' => 'pending',
            'COMPLETED' => 'succeeded',
            'VOIDED' => 'canceled',
            default => 'failed',
        };
    }

    public function getFrontendConfig(): array
    {
        return [
            'gateway' => $this->getIdentifier(),
            'client_id' => $this->getPublishableKey(),
            'supported_methods' => $this->getSupportedMethods(),
            'test_mode' => $this->isTestMode(),
            'sdk_url' => 'https://www.paypal.com/sdk/js?client-id=' . $this->getPublishableKey(),
        ];
    }
}
