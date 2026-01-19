<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Exception;

/**
 * Authorize.net Payment Gateway Implementation
 *
 * Supports: Credit/Debit Cards, eChecks (ACH), Digital Payments
 * Documentation: https://developer.authorize.net/api/reference/
 */
class AuthorizeNetGateway implements PaymentGatewayInterface
{
    protected array $config = [];
    protected bool $initialized = false;
    protected ?AnetAPI\MerchantAuthenticationType $merchantAuth = null;

    public function getIdentifier(): string
    {
        return 'authorizenet';
    }

    public function getName(): string
    {
        return 'Authorize.net';
    }

    public function initialize(array $config): void
    {
        $this->config = $config;

        if (!empty($config['login_id']) && !empty($config['transaction_key'])) {
            $this->merchantAuth = new AnetAPI\MerchantAuthenticationType();
            $this->merchantAuth->setName($config['login_id']);
            $this->merchantAuth->setTransactionKey($config['transaction_key']);
            $this->initialized = true;
        }
    }

    public function isConfigured(): bool
    {
        return $this->initialized
            && !empty($this->config['login_id'])
            && !empty($this->config['transaction_key']);
    }

    public function isTestMode(): bool
    {
        return $this->config['sandbox'] ?? false;
    }

    public function getPublishableKey(): ?string
    {
        // Authorize.net uses Login ID as public identifier
        return $this->config['login_id'] ?? null;
    }

    protected function getEndpoint(): string
    {
        return $this->isTestMode()
            ? \net\authorize\api\constants\ANetEnvironment::SANDBOX
            : \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
    }

    public function createPayment(float $amount, string $currency = 'usd', array $options = []): array
    {
        if (!$this->merchantAuth) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            // Create transaction request
            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType('authCaptureTransaction');
            $transactionRequestType->setAmount(number_format($amount, 2, '.', ''));

            // Accept.js opaque data (tokenized card)
            if (!empty($options['opaque_data'])) {
                $opaqueData = new AnetAPI\OpaqueDataType();
                $opaqueData->setDataDescriptor($options['opaque_data']['data_descriptor']);
                $opaqueData->setDataValue($options['opaque_data']['data_value']);

                $paymentType = new AnetAPI\PaymentType();
                $paymentType->setOpaqueData($opaqueData);
                $transactionRequestType->setPayment($paymentType);
            }
            // Direct card payment (for testing/PCI-compliant environments)
            elseif (!empty($options['card_number'])) {
                $creditCard = new AnetAPI\CreditCardType();
                $creditCard->setCardNumber($options['card_number']);
                $creditCard->setExpirationDate($options['expiration_date']); // YYYY-MM
                if (!empty($options['card_code'])) {
                    $creditCard->setCardCode($options['card_code']);
                }

                $paymentType = new AnetAPI\PaymentType();
                $paymentType->setCreditCard($creditCard);
                $transactionRequestType->setPayment($paymentType);
            }
            // eCheck/ACH payment
            elseif (!empty($options['bank_account'])) {
                $bankAccount = new AnetAPI\BankAccountType();
                $bankAccount->setAccountType($options['bank_account']['account_type'] ?? 'checking');
                $bankAccount->setRoutingNumber($options['bank_account']['routing_number']);
                $bankAccount->setAccountNumber($options['bank_account']['account_number']);
                $bankAccount->setNameOnAccount($options['bank_account']['name_on_account']);
                $bankAccount->setEcheckType('WEB');

                $paymentType = new AnetAPI\PaymentType();
                $paymentType->setBankAccount($bankAccount);
                $transactionRequestType->setPayment($paymentType);
            }

            // Order information
            if (!empty($options['order_id'])) {
                $order = new AnetAPI\OrderType();
                $order->setInvoiceNumber($options['order_id']);
                if (!empty($options['description'])) {
                    $order->setDescription(substr($options['description'], 0, 255));
                }
                $transactionRequestType->setOrder($order);
            }

            // Customer information
            if (!empty($options['customer'])) {
                $customer = new AnetAPI\CustomerDataType();
                if (!empty($options['customer']['email'])) {
                    $customer->setEmail($options['customer']['email']);
                }
                if (!empty($options['customer']['id'])) {
                    $customer->setId($options['customer']['id']);
                }
                $transactionRequestType->setCustomer($customer);
            }

            // Billing address
            if (!empty($options['billing'])) {
                $billTo = new AnetAPI\CustomerAddressType();
                if (!empty($options['billing']['first_name'])) {
                    $billTo->setFirstName($options['billing']['first_name']);
                }
                if (!empty($options['billing']['last_name'])) {
                    $billTo->setLastName($options['billing']['last_name']);
                }
                if (!empty($options['billing']['address'])) {
                    $billTo->setAddress($options['billing']['address']);
                }
                if (!empty($options['billing']['city'])) {
                    $billTo->setCity($options['billing']['city']);
                }
                if (!empty($options['billing']['state'])) {
                    $billTo->setState($options['billing']['state']);
                }
                if (!empty($options['billing']['zip'])) {
                    $billTo->setZip($options['billing']['zip']);
                }
                if (!empty($options['billing']['country'])) {
                    $billTo->setCountry($options['billing']['country']);
                }
                $transactionRequestType->setBillTo($billTo);
            }

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($this->merchantAuth);
            $request->setRefId($options['ref_id'] ?? uniqid('anet_'));
            $request->setTransactionRequest($transactionRequestType);

            $controller = new AnetController\CreateTransactionController($request);
            $response = $controller->executeWithApiResponse($this->getEndpoint());

            if ($response !== null && $response->getMessages()->getResultCode() === 'Ok') {
                $transactionResponse = $response->getTransactionResponse();

                if ($transactionResponse !== null && $transactionResponse->getMessages() !== null) {
                    return [
                        'success' => true,
                        'id' => $transactionResponse->getTransId(),
                        'auth_code' => $transactionResponse->getAuthCode(),
                        'status' => $this->mapStatus($transactionResponse->getResponseCode()),
                        'amount' => $amount,
                        'currency' => $currency,
                        'gateway' => $this->getIdentifier(),
                        'raw' => [
                            'transaction_id' => $transactionResponse->getTransId(),
                            'auth_code' => $transactionResponse->getAuthCode(),
                            'response_code' => $transactionResponse->getResponseCode(),
                            'message' => $transactionResponse->getMessages()[0]->getDescription(),
                        ],
                    ];
                }

                // Transaction errors
                if ($transactionResponse !== null && $transactionResponse->getErrors() !== null) {
                    return [
                        'success' => false,
                        'error' => $transactionResponse->getErrors()[0]->getErrorText(),
                        'code' => $transactionResponse->getErrors()[0]->getErrorCode(),
                        'gateway' => $this->getIdentifier(),
                    ];
                }
            }

            // API-level errors
            $errorMessages = $response->getMessages()->getMessage();
            return [
                'success' => false,
                'error' => $errorMessages[0]->getText() ?? 'Transaction failed',
                'code' => $errorMessages[0]->getCode() ?? 'unknown',
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
        if (!$this->merchantAuth) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $request = new AnetAPI\GetTransactionDetailsRequest();
            $request->setMerchantAuthentication($this->merchantAuth);
            $request->setTransId($paymentId);

            $controller = new AnetController\GetTransactionDetailsController($request);
            $response = $controller->executeWithApiResponse($this->getEndpoint());

            if ($response !== null && $response->getMessages()->getResultCode() === 'Ok') {
                $transaction = $response->getTransaction();

                return [
                    'success' => true,
                    'id' => $transaction->getTransId(),
                    'status' => $this->mapStatus($transaction->getTransactionStatus()),
                    'amount' => (float) $transaction->getSettleAmount(),
                    'currency' => 'usd',
                    'gateway' => $this->getIdentifier(),
                    'raw' => [
                        'transaction_id' => $transaction->getTransId(),
                        'status' => $transaction->getTransactionStatus(),
                        'auth_code' => $transaction->getAuthCode(),
                    ],
                ];
            }

            return [
                'success' => false,
                'error' => 'Transaction not found',
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
        // Authorize.net uses auth + capture in one step by default
        // For auth-only transactions, we would need to capture separately
        return $this->capturePayment($paymentId, $options['amount'] ?? null);
    }

    /**
     * Capture a previously authorized transaction
     */
    public function capturePayment(string $paymentId, ?float $amount = null): array
    {
        if (!$this->merchantAuth) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType('priorAuthCaptureTransaction');
            $transactionRequestType->setRefTransId($paymentId);

            if ($amount !== null) {
                $transactionRequestType->setAmount(number_format($amount, 2, '.', ''));
            }

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($this->merchantAuth);
            $request->setTransactionRequest($transactionRequestType);

            $controller = new AnetController\CreateTransactionController($request);
            $response = $controller->executeWithApiResponse($this->getEndpoint());

            if ($response !== null && $response->getMessages()->getResultCode() === 'Ok') {
                $transactionResponse = $response->getTransactionResponse();

                return [
                    'success' => true,
                    'id' => $transactionResponse->getTransId(),
                    'status' => 'succeeded',
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Capture failed',
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
        if (!$this->merchantAuth) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType('voidTransaction');
            $transactionRequestType->setRefTransId($paymentId);

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($this->merchantAuth);
            $request->setTransactionRequest($transactionRequestType);

            $controller = new AnetController\CreateTransactionController($request);
            $response = $controller->executeWithApiResponse($this->getEndpoint());

            if ($response !== null && $response->getMessages()->getResultCode() === 'Ok') {
                return [
                    'success' => true,
                    'id' => $paymentId,
                    'status' => 'canceled',
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Void failed',
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
        if (!$this->merchantAuth) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            // First get transaction details to get card info
            $details = $this->retrievePayment($paymentId);
            if (!$details['success']) {
                return $details;
            }

            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType('refundTransaction');
            $transactionRequestType->setRefTransId($paymentId);

            if ($amount !== null) {
                $transactionRequestType->setAmount(number_format($amount, 2, '.', ''));
            } else {
                $transactionRequestType->setAmount($details['amount']);
            }

            // For refunds, we need the last 4 of card number
            // This would typically come from stored transaction data
            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber('XXXX'); // Last 4 digits required
            $creditCard->setExpirationDate('XXXX');

            $paymentType = new AnetAPI\PaymentType();
            $paymentType->setCreditCard($creditCard);
            $transactionRequestType->setPayment($paymentType);

            $request = new AnetAPI\CreateTransactionRequest();
            $request->setMerchantAuthentication($this->merchantAuth);
            $request->setTransactionRequest($transactionRequestType);

            $controller = new AnetController\CreateTransactionController($request);
            $response = $controller->executeWithApiResponse($this->getEndpoint());

            if ($response !== null && $response->getMessages()->getResultCode() === 'Ok') {
                $transactionResponse = $response->getTransactionResponse();

                return [
                    'success' => true,
                    'id' => $transactionResponse->getTransId(),
                    'payment_id' => $paymentId,
                    'amount' => $amount ?? $details['amount'],
                    'status' => 'refunded',
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Refund failed',
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
        if (!$this->merchantAuth) {
            return [
                'success' => false,
                'error' => 'Gateway not initialized',
                'gateway' => $this->getIdentifier(),
            ];
        }

        try {
            $customerProfile = new AnetAPI\CustomerProfileType();

            if (!empty($customerData['email'])) {
                $customerProfile->setEmail($customerData['email']);
            }
            if (!empty($customerData['id'])) {
                $customerProfile->setMerchantCustomerId($customerData['id']);
            }
            if (!empty($customerData['description'])) {
                $customerProfile->setDescription($customerData['description']);
            }

            $request = new AnetAPI\CreateCustomerProfileRequest();
            $request->setMerchantAuthentication($this->merchantAuth);
            $request->setProfile($customerProfile);

            $controller = new AnetController\CreateCustomerProfileController($request);
            $response = $controller->executeWithApiResponse($this->getEndpoint());

            if ($response !== null && $response->getMessages()->getResultCode() === 'Ok') {
                return [
                    'success' => true,
                    'id' => $response->getCustomerProfileId(),
                    'email' => $customerData['email'] ?? null,
                    'gateway' => $this->getIdentifier(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to create customer profile',
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
        return ['card', 'echeck'];
    }

    public function verifyWebhook(string $payload, string $signature): array
    {
        try {
            $signatureKey = $this->config['signature_key'] ?? '';

            if (!empty($signatureKey)) {
                // Authorize.net webhook signature verification
                $computedSignature = strtoupper(hash_hmac('sha512', $payload, hex2bin($signatureKey)));

                if (!hash_equals($computedSignature, strtoupper($signature))) {
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
                'type' => $data['eventType'] ?? '',
                'data' => $data['payload'] ?? [],
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
        // Response codes: 1=Approved, 2=Declined, 3=Error, 4=Held for Review
        // Transaction statuses: settledSuccessfully, authorizedPendingCapture, etc.
        return match ($gatewayStatus) {
            '1', 'settledSuccessfully' => 'succeeded',
            '2', 'declined' => 'failed',
            '3', 'communicationError' => 'failed',
            '4', 'FDSPendingReview', 'authorizedPendingCapture' => 'pending',
            'voided' => 'canceled',
            'refundSettledSuccessfully', 'refundPendingSettlement' => 'refunded',
            default => 'pending',
        };
    }

    public function getFrontendConfig(): array
    {
        return [
            'gateway' => $this->getIdentifier(),
            'login_id' => $this->getPublishableKey(),
            'client_key' => $this->config['client_key'] ?? null,
            'supported_methods' => $this->getSupportedMethods(),
            'test_mode' => $this->isTestMode(),
            'accept_js_url' => $this->isTestMode()
                ? 'https://jstest.authorize.net/v1/Accept.js'
                : 'https://js.authorize.net/v1/Accept.js',
        ];
    }

    public function supportsSubscriptions(): bool
    {
        return true;
    }

    public function createSubscription(string $customerId, float $amount, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Gateway not configured',
                'gateway' => $this->getIdentifier(),
            ];
        }

        $trialDays = $options['trial_days'] ?? 14;
        $planName = $options['plan_name'] ?? 'Subscription';
        $interval = $options['interval'] ?? 'month';

        // Calculate start date (after trial)
        $startDate = date('Y-m-d', strtotime("+{$trialDays} days"));

        $request = [
            'ARBCreateSubscriptionRequest' => [
                'merchantAuthentication' => [
                    'name' => $this->config['login_id'],
                    'transactionKey' => $this->config['transaction_key'],
                ],
                'subscription' => [
                    'name' => $planName,
                    'paymentSchedule' => [
                        'interval' => [
                            'length' => $interval === 'year' ? 12 : 1,
                            'unit' => 'months',
                        ],
                        'startDate' => $startDate,
                        'totalOccurrences' => 9999, // Effectively unlimited
                    ],
                    'amount' => number_format($amount, 2, '.', ''),
                    'payment' => [
                        'creditCard' => [
                            'cardNumber' => $options['card_number'] ?? '',
                            'expirationDate' => $options['expiration_date'] ?? '',
                        ],
                    ],
                    'customer' => [
                        'id' => $customerId,
                        'email' => $options['email'] ?? '',
                    ],
                ],
            ],
        ];

        // Add trial period (first billing at $0)
        if ($trialDays > 0) {
            $request['ARBCreateSubscriptionRequest']['subscription']['trialAmount'] = '0.00';
            $request['ARBCreateSubscriptionRequest']['subscription']['paymentSchedule']['trialOccurrences'] = 1;
        }

        $response = $this->apiRequest($request);

        if (!empty($response['subscriptionId'])) {
            return [
                'success' => true,
                'id' => $response['subscriptionId'],
                'status' => 'active',
                'trial_end' => $trialDays > 0 ? date('Y-m-d H:i:s', strtotime("+{$trialDays} days")) : null,
                'gateway' => $this->getIdentifier(),
            ];
        }

        return [
            'success' => false,
            'error' => $response['messages']['message'][0]['text'] ?? 'Failed to create subscription',
            'gateway' => $this->getIdentifier(),
        ];
    }

    public function cancelSubscription(string $subscriptionId, bool $cancelImmediately = false): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Gateway not configured',
                'gateway' => $this->getIdentifier(),
            ];
        }

        $request = [
            'ARBCancelSubscriptionRequest' => [
                'merchantAuthentication' => [
                    'name' => $this->config['login_id'],
                    'transactionKey' => $this->config['transaction_key'],
                ],
                'subscriptionId' => $subscriptionId,
            ],
        ];

        $response = $this->apiRequest($request);

        if (($response['messages']['resultCode'] ?? '') === 'Ok') {
            return [
                'success' => true,
                'id' => $subscriptionId,
                'status' => 'canceled',
                'gateway' => $this->getIdentifier(),
            ];
        }

        return [
            'success' => false,
            'error' => $response['messages']['message'][0]['text'] ?? 'Failed to cancel subscription',
            'gateway' => $this->getIdentifier(),
        ];
    }

    public function resumeSubscription(string $subscriptionId): array
    {
        // Authorize.net doesn't support resuming canceled subscriptions
        // A new subscription must be created
        return [
            'success' => false,
            'error' => 'Authorize.net requires creating a new subscription to resume',
            'gateway' => $this->getIdentifier(),
        ];
    }

    public function retrieveSubscription(string $subscriptionId): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Gateway not configured',
                'gateway' => $this->getIdentifier(),
            ];
        }

        $request = [
            'ARBGetSubscriptionRequest' => [
                'merchantAuthentication' => [
                    'name' => $this->config['login_id'],
                    'transactionKey' => $this->config['transaction_key'],
                ],
                'subscriptionId' => $subscriptionId,
            ],
        ];

        $response = $this->apiRequest($request);

        if (!empty($response['subscription'])) {
            $sub = $response['subscription'];
            return [
                'success' => true,
                'id' => $subscriptionId,
                'status' => strtolower($sub['status'] ?? 'active'),
                'name' => $sub['name'] ?? '',
                'amount' => $sub['amount'] ?? 0,
                'gateway' => $this->getIdentifier(),
            ];
        }

        return [
            'success' => false,
            'error' => $response['messages']['message'][0]['text'] ?? 'Subscription not found',
            'gateway' => $this->getIdentifier(),
        ];
    }
}
