# Subscription System

## Overview

The subscription system provides tiered pricing with feature gates, usage limits, and Stripe integration for payment processing. Users can subscribe to plans that unlock additional features and higher limits.

## Pricing Tiers

| Plan | Price | Events | Entries | Trial |
|------|-------|--------|---------|-------|
| **Free Trial** | $0/mo | 1 | 20 | - |
| **Non-Profit** | $9.99/mo | 3 | 100 | 14 days |
| **Professional** | $29.99/mo | 10 | Unlimited | 14 days |
| **Premium** | $59.00/mo | Unlimited | Unlimited | Contact Sales |

## Feature Matrix

| Feature | Free | Non-Profit | Professional | Premium |
|---------|------|------------|--------------|---------|
| Basic Voting | Yes | Yes | Yes | Yes |
| All Voting Types | No | Yes | Yes | Yes |
| Real-time Results | Yes | Yes | Yes | Yes |
| Custom Templates | Yes | Yes | Yes | Yes |
| PDF Ballots | Yes | Yes | Yes | Yes |
| Excel Import | No | Yes | Yes | Yes |
| Judging Panels | No | No | Yes | Yes |
| Advanced Analytics | No | No | Yes | Yes |
| White-label | No | No | No | Yes |
| API Access | No | No | No | Yes |
| Custom Integrations | No | No | No | Yes |

## Support Levels

| Plan | Support Level |
|------|---------------|
| Free Trial | Community |
| Non-Profit | Email Support |
| Professional | Priority Support |
| Premium | Dedicated Support |

## Database Schema

### subscription_plans

```sql
CREATE TABLE subscription_plans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(255) UNIQUE,           -- free, nonprofit, professional, premium
    name VARCHAR(255),
    description VARCHAR(255),
    price DECIMAL(8,2) DEFAULT 0,
    billing_period VARCHAR(255),        -- monthly, yearly
    stripe_price_id VARCHAR(255),

    -- Limits
    max_events INT DEFAULT 1,           -- -1 for unlimited
    max_entries_per_event INT DEFAULT 20,

    -- Feature Flags
    has_basic_voting BOOLEAN DEFAULT TRUE,
    has_all_voting_types BOOLEAN DEFAULT FALSE,
    has_realtime_results BOOLEAN DEFAULT TRUE,
    has_custom_templates BOOLEAN DEFAULT FALSE,
    has_pdf_ballots BOOLEAN DEFAULT TRUE,
    has_excel_import BOOLEAN DEFAULT FALSE,
    has_judging_panels BOOLEAN DEFAULT FALSE,
    has_advanced_analytics BOOLEAN DEFAULT FALSE,
    has_white_label BOOLEAN DEFAULT FALSE,
    has_api_access BOOLEAN DEFAULT FALSE,
    has_custom_integrations BOOLEAN DEFAULT FALSE,

    -- Display
    support_level VARCHAR(255),
    display_order INT,
    is_popular BOOLEAN,
    is_active BOOLEAN,
    cta_text VARCHAR(255),
    cta_style VARCHAR(255),

    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### user_subscriptions

```sql
CREATE TABLE user_subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT REFERENCES users(id),
    subscription_plan_id BIGINT REFERENCES subscription_plans(id),

    -- Stripe Data
    stripe_subscription_id VARCHAR(255),
    stripe_customer_id VARCHAR(255),

    -- Status
    status VARCHAR(255),                -- active, canceled, past_due, trialing
    trial_ends_at TIMESTAMP,
    current_period_start TIMESTAMP,
    current_period_end TIMESTAMP,
    canceled_at TIMESTAMP,
    ended_at TIMESTAMP,

    -- Payment Method
    payment_method_brand VARCHAR(255),
    payment_method_last4 VARCHAR(4),

    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Key Files

| File | Purpose |
|------|---------|
| `app/Models/SubscriptionPlan.php` | Plan model with features |
| `app/Models/UserSubscription.php` | User subscription model |
| `app/Http/Controllers/SubscriptionController.php` | Subscription management |
| `app/Http/Middleware/CheckPlanLimits.php` | Feature gate middleware |
| `app/Http/Middleware/CheckEventLimit.php` | Event limit enforcement |
| `resources/views/subscription/pricing.blade.php` | Pricing page |
| `resources/views/subscription/manage.blade.php` | User subscription management |
| `database/seeders/SubscriptionPlanSeeder.php` | Default plans |

## Routes

```
GET  /pricing                              - Public pricing page
GET  /subscription/manage                  - User subscription management
POST /subscription/subscribe/{plan}        - Subscribe to plan
GET  /subscription/success                 - Post-checkout success
POST /subscription/cancel                  - Cancel subscription
POST /subscription/resume                  - Resume canceled subscription
POST /subscription/billing-portal          - Stripe billing portal
GET  /subscription/stripe-key              - Get publishable key
POST /webhook/stripe                       - Stripe webhooks
```

## User Model Methods

```php
// Check current plan
$user->currentPlan();              // Returns SubscriptionPlan or free plan
$user->activeSubscription();       // Returns UserSubscription or null
$user->isSubscribed();             // Boolean
$user->isOnFreePlan();             // Boolean
$user->isOnPaidPlan();             // Boolean

// Feature Checks
$user->hasFeature('judging_panels'); // Boolean
$user->canCreateEvent();           // Check event limit
$user->canAddEntries($event, 5);   // Check entry limit

// Usage Stats
$user->getRemainingEvents();       // Int or "Unlimited"
$user->getRemainingEntriesForEvent($event); // Int or "Unlimited"
```

## Middleware Usage

### Feature Gate Middleware

```php
// In routes/web.php
Route::middleware(['plan.feature:judging_panels'])->group(function () {
    Route::get('/admin/judges', [JudgeController::class, 'index']);
});
```

### Event Limit Middleware

```php
// Automatically applied to event creation
Route::middleware(['plan.events'])->group(function () {
    Route::post('/admin/events', [EventController::class, 'store']);
});
```

## Stripe Integration

### Checkout Flow

1. User clicks "Subscribe" on pricing page
2. Backend creates Stripe Checkout Session
3. User redirected to Stripe Checkout
4. After payment, redirected to success URL
5. Backend creates UserSubscription record

### Webhook Events

| Event | Action |
|-------|--------|
| `customer.subscription.updated` | Update subscription status/dates |
| `customer.subscription.deleted` | Mark subscription ended |
| `invoice.payment_succeeded` | Clear past_due status |
| `invoice.payment_failed` | Set past_due status |

### Billing Portal

Users can access Stripe's billing portal to:
- Update payment method
- View invoices
- Download receipts
- Cancel subscription

## Subscription Status

| Status | Description |
|--------|-------------|
| `active` | Subscription is active and paid |
| `trialing` | Within trial period |
| `past_due` | Payment failed, grace period |
| `canceled` | Will end at period end |
| `paused` | Temporarily paused |

## Plan Limit Enforcement

### Events

```php
// In User model
public function canCreateEvent(): bool
{
    $plan = $this->currentPlan();
    if ($plan->isUnlimitedEvents()) return true;

    $activeEvents = $this->createdEvents()
        ->where('is_active', true)
        ->count();

    return $activeEvents < $plan->max_events;
}
```

### Entries

```php
// In User model
public function canAddEntries(Event $event, int $count = 1): bool
{
    $plan = $this->currentPlan();
    if ($plan->isUnlimitedEntries()) return true;

    $currentEntries = $event->entries()->count();
    return ($currentEntries + $count) <= $plan->max_entries_per_event;
}
```

## UI Components

### Pricing Page

Located at `/pricing`, displays:
- All active plans in responsive grid
- Feature comparison
- Current plan indicator
- Subscribe/Upgrade buttons
- FAQ section

### Management Page

Located at `/subscription/manage`, displays:
- Current plan summary
- Subscription status
- Usage statistics (events, entries)
- Billing actions (cancel, resume, portal)
- Upgrade options

## Testing

### Test Subscriptions

1. Configure Stripe test keys
2. Use test card: `4242 4242 4242 4242`
3. Subscribe to any plan
4. Verify subscription in database
5. Test feature gates

### Test Scenarios

- [ ] Subscribe to paid plan
- [ ] Cancel subscription (keeps access)
- [ ] Resume canceled subscription
- [ ] Exceed event limit (blocked)
- [ ] Access gated feature (blocked)
- [ ] Upgrade plan mid-cycle
- [ ] Downgrade plan

## Related Documentation

- [Payment Processing](PAYMENT_PROCESSING.md)
- [Implementation Details](IMPLEMENTATION.md)
