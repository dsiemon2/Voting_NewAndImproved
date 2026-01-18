# Voting Application - Project Requirements

## Overview
This is a Laravel 11 rebuild of the legacy PHP voting application at `C:\xampp\htdocs\vote_TGBO`.
Reference old site at: http://localhost:3000/vote_TGBO/

## Critical Requirements

### 1. Voting Page (`/vote/{event}`)
- **MUST have side-by-side voting boxes** for each division type (Professional/Amateur)
- Each voting box has input fields for each place (1st, 2nd, 3rd, etc.)
- Number of inputs is determined by the **Voting Type's place configurations**
- User enters division NUMBER (e.g., 1, 2, 3) not entry ID
- Submit Vote button in center
- Results tables below showing current standings

### 2. Division Types
- Defined in `event_templates.division_types` JSON field
- Each division has a `type` field matching template types
- Food Competition: Professional (P), Amateur (A)
- Photo Contest: Nature (N), Portrait (P), Street (S)
- Talent Show: Vocal (V), Instrumental (I), Dance (D)

### 3. Voting Types (Dynamic Point Systems)
- Standard Ranked: 3-2-1 (3 places)
- Extended Ranked: 5-4-3-2-1 (5 places)
- Top-Heavy: 5-3-1 (3 places)
- **ALWAYS check event has voting config with voting_type_id set**
- Use `getPlaceConfigs()` and convert to `[place => points]` format

### 4. Results Page (`/results/{event}`)
- Side-by-side results tables grouped by division type
- Shows rank, entry name, participant name, division code, points
- Shows vote counts per place (1st, 2nd, 3rd columns)

### 5. Template Labels
- Use template's `participant_label` (Chef, Photographer, etc.)
- Use template's `entry_label` (Entry, Photo, Submission, etc.)
- **NEVER use "Dish" - always use "Entry"**

## Common Mistakes to Avoid

1. **Empty voting boxes**: Always ensure event has `voting_type_id` in `event_voting_configs`
2. **getPlaceConfigs() format**: Returns array of objects `[{place, points, label, color, icon}]`,
   must convert to `[place => points]` for views
3. **Lazy loading errors**: Always eager load relationships in controllers
4. **Missing division types**: Check divisions have `type` field set matching template

## Key Files

### Voting System
- **Voting Controller**: `app/Http/Controllers/Voting/VoteController.php`
- **Results Controller**: `app/Http/Controllers/Voting/ResultsController.php`
- **Vote View**: `resources/views/voting/vote.blade.php`
- **Results View**: `resources/views/results/index.blade.php`
- **Event Template Model**: `app/Models/EventTemplate.php`
- **Voting Type Model**: `app/Models/VotingType.php`

### AI Chat System
- **Chat Controller**: `app/Http/Controllers/Api/AiChatController.php`
- **AI Service**: `app/Services/AI/AiService.php`
- **Context Builder**: `app/Services/AI/AiContextBuilder.php`
- **Wizard State Machine**: `app/Services/AI/WizardStateMachine.php`
- **Chat UI Component**: `resources/views/components/ai-chat-slider.blade.php`
- **Provider Model**: `app/Models/AiProvider.php`
- **AI Providers Page**: `resources/views/admin/ai-providers/index.blade.php`

### Payment Processing System
- **Payment Gateway Model**: `app/Models/PaymentGateway.php`
- **Payment Controller**: `app/Http/Controllers/Admin/PaymentGatewayController.php`
- **Payment Admin Page**: `resources/views/admin/payment-processing/index.blade.php`
- **Migration**: `database/migrations/2026_01_15_131110_create_payment_gateways_table.php`
- **Seeder**: `database/seeders/PaymentGatewaySeeder.php`

### Subscription System
- **Subscription Plan Model**: `app/Models/SubscriptionPlan.php`
- **User Subscription Model**: `app/Models/UserSubscription.php`
- **Subscription Controller**: `app/Http/Controllers/SubscriptionController.php`
- **Pricing Page**: `resources/views/subscription/pricing.blade.php`
- **Subscription Management**: `resources/views/subscription/manage.blade.php`
- **Plan Limits Middleware**: `app/Http/Middleware/CheckPlanLimits.php`
- **Event Limit Middleware**: `app/Http/Middleware/CheckEventLimit.php`
- **Seeder**: `database/seeders/SubscriptionPlanSeeder.php`

## Database Requirements

### Event must have:
```sql
-- Voting config with type
INSERT INTO event_voting_configs (event_id, voting_type_id) VALUES (1, 1);

-- Divisions with type set
UPDATE divisions SET type = 'Professional' WHERE code LIKE 'P%';
UPDATE divisions SET type = 'Amateur' WHERE code LIKE 'A%';
```

### Entry Number Convention
- Professional entries: 1-99 (e.g., P1=1, P2=2, P13=13)
- Amateur entries: 101-199 (e.g., A1=101, A2=102, A13=113)
- Entry numbers must be unique per event

### Sample Data (via OldDatabaseSeeder)
- Soup Cookoff: P1-P13, A1-A13 (26 entries total)
- Great Bakeoff: P1-P10, A1-A10 (20 entries total)

## Styling

- Use colors from old site: `#2c3e50` (dark blue), `#34495e` (gray-blue), `#1e40af` (blue), `#ff6600` (orange)
- Side-by-side boxes using flexbox
- Responsive: stack on mobile

## Testing Checklist

### Voting System
- [ ] Voting page shows side-by-side voting boxes
- [ ] Input fields match voting type's place count
- [ ] Results tables show below voting form
- [ ] Division types display correctly (Professional/Amateur)
- [ ] Points system matches voting type (3-2-1, 5-4-3-2-1, etc.)

### AI Chat System
- [ ] Chat slider opens/closes correctly
- [ ] AI responds to natural language queries
- [ ] Voice input records and transcribes (requires OpenAI key)
- [ ] Wizards complete multi-step operations
- [ ] Event switching refreshes page correctly
- [ ] Results queries return correct event data

### Payment Processing
- [ ] Payment processing page accessible at `/admin/payment-processing`
- [ ] Stripe gateway configured and enabled
- [ ] API keys can be saved and updated
- [ ] Test connection works for configured gateways
- [ ] Only one gateway can be active at a time

### Subscription System
- [ ] Pricing page displays all 4 plans correctly
- [ ] Subscribe button redirects to Stripe Checkout
- [ ] Subscription management page shows current plan
- [ ] Cancel/Resume subscription works
- [ ] Plan limits enforced (events, entries)
- [ ] Feature gates work based on plan

## Subscription Plans

| Plan | Price | Events | Entries | Key Features |
|------|-------|--------|---------|--------------|
| Free Trial | $0/mo | 1 | 20 | Basic Voting, Real-time Results, Custom Templates, PDF Ballots |
| Non-Profit | $9.99/mo | 3 | 100 | All Voting Types, Excel Import, Email Support |
| Professional | $29.99/mo | 10 | Unlimited | Judging Panels, Advanced Analytics, Priority Support |
| Premium | $59.00/mo | Unlimited | Unlimited | White-label, API Access, Custom Integrations, Dedicated Support |

## Payment Gateways

All 5 payment gateways are fully integrated:

| Gateway | Status | Location |
|---------|--------|----------|
| **Stripe** | Full integration | `app/Services/Payments/StripeGateway.php` |
| **PayPal** | Full integration | `app/Services/Payments/PayPalGateway.php` |
| **Braintree** | Full integration | `app/Services/Payments/BraintreeGateway.php` |
| **Square** | Full integration | `app/Services/Payments/SquareGateway.php` |
| **Authorize.net** | Full integration | `app/Services/Payments/AuthorizeNetGateway.php` |

### Payment Services Location
```
app/Services/Payments/
├── StripeGateway.php       # Stripe payment processing
├── PayPalGateway.php       # PayPal order management
├── BraintreeGateway.php    # Braintree transactions
├── SquareGateway.php       # Square payment processing
├── AuthorizeNetGateway.php # Authorize.net processing
└── PaymentManager.php      # Unified payment orchestrator
```

### Payment Gateway Interface
All gateways implement `App\Contracts\PaymentGatewayInterface`:
- `createPayment()` - Create payment intent/transaction
- `retrievePayment()` - Get payment details
- `confirmPayment()` - Capture authorized payment
- `cancelPayment()` - Void/cancel payment
- `refundPayment()` - Process refunds
- `createCustomer()` - Create customer record
- `verifyWebhook()` - Validate webhook signatures

Configured in `/admin/payment-processing`:
- **Stripe** (Default) - 2.9% + 30c
- **PayPal** - 2.9% + 30c
- **Braintree** - 2.59% + 49c
- **Square** - 2.6% + 10c
- **Authorize.net** - 2.9% + 30c
