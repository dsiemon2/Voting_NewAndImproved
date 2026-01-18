# Payment Processing System

## Overview

The Voting Application includes a comprehensive payment processing system supporting multiple payment gateways. This allows event organizers to accept payments for subscriptions and premium features.

## Supported Payment Gateways

| Provider | Fee | Best For | Key Features |
|----------|-----|----------|--------------|
| **Stripe** | 2.9% + 30c | Most users | Cards, ACH, Apple Pay, Google Pay |
| **Braintree** | 2.59% + 49c | PayPal users | Cards, PayPal, Venmo |
| **Square** | 2.6% + 10c | POS integration | Cards, Cash App Pay |
| **Authorize.net** | 2.9% + 30c | Enterprise | Cards, eChecks |

## Configuration

### Admin Panel

Access the payment configuration at:
```
/admin/payment-processing
```

Only administrators can configure payment gateways.

### Database Schema

```sql
CREATE TABLE payment_gateways (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    provider VARCHAR(255) UNIQUE,        -- stripe, braintree, square, authorize
    is_enabled BOOLEAN DEFAULT FALSE,
    publishable_key VARCHAR(255),
    secret_key TEXT,                     -- Encrypted
    test_mode BOOLEAN DEFAULT TRUE,
    ach_enabled BOOLEAN DEFAULT FALSE,   -- Stripe only
    webhook_secret VARCHAR(255),
    merchant_id VARCHAR(255),            -- Braintree/Square only
    additional_config TEXT,              -- JSON for provider-specific settings
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Gateway Configuration

### Stripe (Default)

1. Get API keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys)
2. Enter Publishable Key (starts with `pk_live_` or `pk_test_`)
3. Enter Secret Key (starts with `sk_live_` or `sk_test_`)
4. Optionally enable ACH bank transfers
5. Configure webhook secret for event notifications

**Environment Variables:**
Configure in your `.env` file:
```
STRIPE_PUBLISHABLE_KEY=pk_test_your_key_here
STRIPE_SECRET_KEY=sk_test_your_key_here
STRIPE_TEST_MODE=true
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

**Important:** Never commit live API keys to version control!

### Braintree

1. Get credentials from [Braintree Control Panel](https://www.braintreegateway.com/login)
2. Enter Public Key
3. Enter Private Key
4. Enter Merchant ID

### Square

1. Get credentials from [Square Developer Dashboard](https://squareup.com/dashboard/apps/my-applications)
2. Enter Application ID
3. Enter Access Token
4. Enter Location ID (optional)

### Authorize.net

1. Get credentials from Authorize.net Merchant Interface
2. Enter API Login ID
3. Enter Transaction Key
4. Configure Accept.js for tokenization

## Key Files

| File | Purpose |
|------|---------|
| `app/Models/PaymentGateway.php` | Gateway model with settings |
| `app/Http/Controllers/Admin/PaymentGatewayController.php` | Admin controller |
| `resources/views/admin/payment-processing/index.blade.php` | Admin UI |
| `database/migrations/2026_01_15_*_create_payment_gateways_table.php` | Schema |
| `database/seeders/PaymentGatewaySeeder.php` | Default gateway config |

## API Reference

### Routes

```
GET  /admin/payment-processing              - Configuration page
GET  /admin/payment-processing/gateways     - Get all gateways (JSON)
POST /admin/payment-processing/{provider}   - Update gateway config
POST /admin/payment-processing/{provider}/enable  - Enable gateway
POST /admin/payment-processing/{provider}/disable - Disable gateway
POST /admin/payment-processing/{provider}/test    - Test connection
```

### PaymentGateway Model

```php
// Get active provider
$gateway = PaymentGateway::where('is_enabled', true)->first();

// Get all providers
$providers = PaymentGateway::getProviders(); // ['stripe', 'braintree', 'square', 'authorize']

// Check if configured
if ($gateway->publishable_key && $gateway->secret_key) {
    // Ready to process payments
}
```

## Security Considerations

1. **API Key Storage**: Secret keys are stored in the database, not environment files
2. **Single Provider**: Only one gateway can be active at a time to prevent conflicts
3. **Test Mode**: Always test with sandbox/test keys before going live
4. **Webhooks**: Configure webhook secrets to verify incoming events
5. **PCI Compliance**: Tokens are handled by the gateway SDKs, not stored locally

## Testing

### Stripe Test Cards

| Card Number | Description |
|-------------|-------------|
| 4242 4242 4242 4242 | Success |
| 4000 0000 0000 0002 | Decline |
| 4000 0027 6000 3184 | 3D Secure |

Use any future expiration date and any 3-digit CVC.

### Test Mode

All gateways support test/sandbox mode:
- Stripe: Use `pk_test_` and `sk_test_` keys
- Braintree: Use sandbox credentials
- Square: Use sandbox environment
- Authorize.net: Use sandbox endpoint

## Webhooks

### Stripe Webhook Events

Configure webhook endpoint at:
```
POST /webhook/stripe
```

Supported events:
- `customer.subscription.updated`
- `customer.subscription.deleted`
- `invoice.payment_succeeded`
- `invoice.payment_failed`

### Configuration

1. Go to [Stripe Webhook Dashboard](https://dashboard.stripe.com/webhooks)
2. Add endpoint: `https://yourdomain.com/webhook/stripe`
3. Select events to listen for
4. Copy signing secret to Payment Processing settings

## Troubleshooting

### "Payment processing not configured"

- Ensure a gateway is enabled
- Verify API keys are entered
- Check secret key is set

### "Invalid API key"

- Verify key matches environment (live vs test)
- Check for extra spaces or characters
- Regenerate keys if necessary

### Webhook signature verification failed

- Verify webhook secret matches Stripe dashboard
- Check request is coming from Stripe IP ranges
- Ensure raw body is used for signature verification

## Related Documentation

- [Subscription System](SUBSCRIPTION_SYSTEM.md)
- [Implementation Details](IMPLEMENTATION.md)
