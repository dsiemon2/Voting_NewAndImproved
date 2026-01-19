# Trial Code System - Implementation Plan

## Overview

This document outlines the complete Trial Code System for the Voting Application, including the user flow, admin features, and all related changes to the landing page, pricing page, and registration system.

---

## Part 1: Previous Changes (Landing, Pricing, Registration)

### 1.1 Landing Page Changes (`/`)

**File:** `resources/views/landing.blade.php`

**Button Label Changes:**
| Plan | Old Label | New Label | Action |
|------|-----------|-----------|--------|
| Free Trial ($0) | "Get Started Free" | "Get Trial Code" | Links to trial code request page |
| Non-Profit ($9.99) | "Start Free Trial" | "Start Plan" | Links to registration with plan |
| Professional ($29.99) | "Start Free Trial" | "Start Plan" | Links to registration with plan |
| Premium ($59.00) | "Contact Sales" | "Start Plan" | Links to registration with plan |

**Link Destinations:**
- Free Trial: `/trial-code/request` (NEW)
- Paid Plans: `/register?plan={plan_code}`

### 1.2 Pricing Page Changes (`/pricing`)

**File:** `resources/views/subscription/pricing.blade.php`

**Button Behavior:**
- Not logged in: Links to `/register?plan={plan_code}` with plan pre-selected
- Logged in (current plan): Shows "Current Plan" (disabled)
- Logged in (upgrade/downgrade): Shows "Upgrade" or "Downgrade" button

**Database Updates:**
- `subscription_plans.cta_text` updated:
  - Free: "Get Trial Code"
  - Non-Profit: "Start Plan"
  - Professional: "Start Plan"
  - Premium: "Start Plan"

### 1.3 Registration Page Changes (`/register`)

**File:** `resources/views/auth/register.blade.php`
**Controller:** `app/Http/Controllers/Auth/AuthController.php`

**Features:**
- Plan selection radio buttons (pre-selected from URL parameter)
- Plan badge showing selected plan name and price
- Hidden `plan` field submitted with form
- **NEW:** Trial code input field (for Free Trial plan)

**Post-Registration Logic:**
- Free plan ($0): Creates subscription directly, redirects to dashboard
- Paid plans: Redirects to pricing page for Stripe checkout
- Trial code: Validates and applies 14-day trial

---

## Part 2: Payment Gateway Subscription Support

### 2.1 Interface Updates

**File:** `app/Contracts/PaymentGatewayInterface.php`

**New Methods Added:**
```php
public function createSubscription(string $customerId, float $amount, array $options = []): array;
public function cancelSubscription(string $subscriptionId, bool $cancelImmediately = false): array;
public function resumeSubscription(string $subscriptionId): array;
public function retrieveSubscription(string $subscriptionId): array;
public function supportsSubscriptions(): bool;
```

### 2.2 Gateway Implementations

All 5 payment gateways now support recurring subscriptions with 14-day free trials:

| Gateway | File | Subscription API Used |
|---------|------|----------------------|
| Stripe | `StripeGateway.php` | Stripe Subscriptions API |
| PayPal | `PayPalGateway.php` | PayPal Billing Plans API |
| Braintree | `BraintreeGateway.php` | Braintree Plans/Subscriptions |
| Square | `SquareGateway.php` | Square Subscriptions API |
| Authorize.net | `AuthorizeNetGateway.php` | ARB (Automated Recurring Billing) |

### 2.3 Trial Period Configuration

**Default:** 14-day free trial on all paid plans
**Logic Location:** `SubscriptionController.php` lines 134-139

```php
if ($plan->price > 0 && !$currentSubscription) {
    $sessionParams['subscription_data'] = [
        'trial_period_days' => 14,
    ];
}
```

---

## Part 3: Trial Code System (NEW)

### 3.1 User Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           TRIAL CODE USER FLOW                               │
└─────────────────────────────────────────────────────────────────────────────┘

[Landing Page] ──► "Get Trial Code" button
                         │
                         ▼
              ┌─────────────────────┐
              │  Trial Code Request │
              │       Page          │
              │  (/trial-code/request) │
              └─────────────────────┘
                         │
         User enters: Name, Email, Phone (optional)
         Selects delivery: Email or SMS
                         │
                         ▼
              ┌─────────────────────┐
              │  System Validates   │
              │  - Email not used   │
              │  - No existing code │
              └─────────────────────┘
                         │
            ┌────────────┴────────────┐
            │                         │
      [Email exists]            [New Email]
            │                         │
            ▼                         ▼
   "Trial code already         Generate unique
    sent to this email"        trial code (8 chars)
                                      │
                                      ▼
                              Store in database
                              (trial_codes table)
                                      │
                                      ▼
                         ┌────────────┴────────────┐
                         │                         │
                   [Email selected]          [SMS selected]
                         │                         │
                         ▼                         ▼
                  Send email with:          Send SMS with:
                  - Trial code              - Trial code
                  - Registration link       - Registration link
                  - Expiration info         - Expiration info
                                      │
                                      ▼
              ┌─────────────────────┐
              │  Confirmation Page  │
              │  "Check your email/ │
              │   phone for code"   │
              └─────────────────────┘
                         │
                         ▼
              ┌─────────────────────┐
              │  Registration Page  │
              │  (/register?plan=free) │
              │                     │
              │  [Trial Code Field] │
              │  shown for Free plan│
              └─────────────────────┘
                         │
         User enters trial code + account info
                         │
                         ▼
              ┌─────────────────────┐
              │  Validate Code      │
              │  - Code exists      │
              │  - Not expired      │
              │  - Not redeemed     │
              │  - Email matches    │
              └─────────────────────┘
                         │
            ┌────────────┴────────────┐
            │                         │
      [Invalid Code]            [Valid Code]
            │                         │
            ▼                         ▼
     Show error message      Create account with
                             14-day free trial
                             Mark code as redeemed
                                      │
                                      ▼
                              ┌───────────────┐
                              │   Dashboard   │
                              │ (Trial Active)│
                              └───────────────┘
```

### 3.2 Admin Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           ADMIN TRIAL CODE FLOW                              │
└─────────────────────────────────────────────────────────────────────────────┘

[Admin Dashboard] ──► "Trial Codes" menu
                            │
                            ▼
              ┌─────────────────────────┐
              │   Trial Codes List      │
              │   (/admin/trial-codes)  │
              │                         │
              │  Columns:               │
              │  - Code                 │
              │  - Name                 │
              │  - Email                │
              │  - Phone                │
              │  - Status               │
              │  - Created At           │
              │  - Redeemed At          │
              │  - Expires At           │
              │  - Extension Count      │
              │  - Actions              │
              └─────────────────────────┘
                            │
         ┌──────────────────┼──────────────────┐
         │                  │                  │
         ▼                  ▼                  ▼
   [View Details]    [Extend Trial]    [Revoke Code]
         │                  │                  │
         ▼                  ▼                  ▼
   Show full info    ┌─────────────┐    Mark as revoked
   + activity log    │ Check:      │
                     │ - < 3 ext.  │
                     │ - Not expired│
                     └─────────────┘
                            │
               ┌────────────┴────────────┐
               │                         │
         [Can Extend]             [Cannot Extend]
               │                         │
               ▼                         ▼
        Generate NEW code         Show error:
        + 14 days from now        "Max extensions
        Increment ext. count       reached (3)"
        Send notification
```

### 3.3 Database Schema

#### New Table: `trial_codes`

```sql
CREATE TABLE trial_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Trial Code (format: XXXX-XXXX)
    code VARCHAR(10) NOT NULL UNIQUE,

    -- Requester Info (captured before registration)
    requester_first_name VARCHAR(100) NOT NULL,
    requester_last_name VARCHAR(100) NOT NULL,
    requester_email VARCHAR(255) NOT NULL,
    requester_phone VARCHAR(20) NULL,
    requester_organization VARCHAR(255) NULL,
    delivery_method ENUM('email', 'sms') DEFAULT 'email',

    -- Linked User (set after registration/redemption)
    -- FOREIGN KEY: Links to users.id when code is redeemed
    user_id BIGINT UNSIGNED NULL,

    -- Status
    status ENUM('pending', 'sent', 'redeemed', 'expired', 'revoked') DEFAULT 'pending',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    redeemed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NOT NULL,

    -- Extension tracking
    extension_count TINYINT UNSIGNED DEFAULT 0,
    -- FOREIGN KEY: Links to users.id (admin who extended)
    extended_by BIGINT UNSIGNED NULL,
    last_extended_at TIMESTAMP NULL,
    -- FOREIGN KEY: Links to trial_codes.id (original code if this is an extension)
    parent_code_id BIGINT UNSIGNED NULL,

    -- Audit
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,

    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (extended_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_code_id) REFERENCES trial_codes(id) ON DELETE SET NULL,

    -- Indexes
    INDEX idx_requester_email (requester_email),
    INDEX idx_requester_phone (requester_phone),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
);
```

### 3.3.1 Database Relationships

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        DATABASE RELATIONSHIPS                                │
└─────────────────────────────────────────────────────────────────────────────┘

trial_codes                              users
┌─────────────────┐                     ┌─────────────────┐
│ id              │                     │ id              │
│ code            │                     │ first_name      │
│ requester_*     │                     │ last_name       │
│ user_id ────────┼──────────────────► │ email           │
│ extended_by ────┼──────────────────► │ ...             │
│ parent_code_id  │                     └─────────────────┘
│ ...             │
└────────┬────────┘
         │
         └──────────────────────────────────────┐
                                                │
                                                ▼
                                    (self-reference for extensions)
```

**Eloquent Relationships (TrialCode model):**
```php
// User who redeemed this trial code
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// Admin who extended this trial
public function extendedByAdmin(): BelongsTo
{
    return $this->belongsTo(User::class, 'extended_by');
}

// Original trial code (if this is an extension)
public function parentCode(): BelongsTo
{
    return $this->belongsTo(TrialCode::class, 'parent_code_id');
}

// Extension codes created from this code
public function extensionCodes()
{
    return $this->hasMany(TrialCode::class, 'parent_code_id');
}
```

**Key Points:**
- `user_id` is NULL until the trial code is redeemed during registration
- When redeemed, `user_id` links to the newly created user account
- `extended_by` always links to an Administrator user
- Admin view displays both requester info AND linked user info

### 3.4 Business Rules

#### Rule 1: One Trial Code Per Email/Phone (Automated)
```
IF requester_email EXISTS in trial_codes WHERE status NOT IN ('expired', 'revoked')
THEN reject request with message "Trial code already issued to this email"
```

#### Rule 2: Admin Extensions (Manual)
```
IF extension_count < 3 AND status = 'redeemed' AND trial not expired
THEN admin CAN generate new extension code
     - New code created with parent_code_id = original code
     - extension_count incremented
     - User notified of extension
```

#### Rule 3: Code Expiration
```
Trial code EXPIRES:
- If not redeemed: 7 days after creation
- If redeemed: 14 days after registration (trial period)
```

#### Rule 4: Code Format
```
Format: XXXX-XXXX (8 alphanumeric characters)
Example: TRL8-K9M2
Characters: A-Z, 0-9 (excluding confusing chars: 0, O, I, L, 1)
```

### 3.5 Files to Create/Modify

#### New Files (Created):
```
app/
├── Http/
│   └── Controllers/
│       ├── TrialCodeController.php          # User-facing trial code requests ✓
│       └── Admin/
│           ├── TrialCodeController.php      # Admin trial code management ✓
│           └── TwilioSettingsController.php # Twilio configuration ✓
├── Models/
│   ├── TrialCode.php                        # Trial code Eloquent model ✓
│   └── SystemSetting.php                    # System settings model ✓
├── Services/
│   └── TrialCodeService.php                 # Business logic ✓
│
database/
├── migrations/
│   ├── 2026_01_19_200000_create_trial_codes_table.php ✓
│   └── 2026_01_19_210000_create_system_settings_table.php ✓
│
resources/
└── views/
    ├── trial-code/
    │   ├── request.blade.php                # Request form (modal component)
    │   └── confirmation.blade.php           # Confirmation page
    ├── components/
    │   └── trial-code-modal.blade.php       # Trial code request modal
    └── admin/
        ├── trial-codes/
        │   ├── index.blade.php              # List all codes
        │   └── show.blade.php               # Code details
        └── twilio-settings/
            └── index.blade.php              # Twilio configuration page
```

#### Modified Files:
```
resources/views/
├── landing.blade.php                        # Update "Get Trial Code" link
├── subscription/pricing.blade.php           # Update Free plan button
└── auth/register.blade.php                  # Add trial code field

app/Http/Controllers/Auth/
└── AuthController.php                       # Validate trial code on registration

routes/
└── web.php                                  # Add trial code routes
```

### 3.6 Routes

```php
// Trial Code Routes (Public/AJAX)
Route::prefix('trial-code')->name('trial-code.')->group(function () {
    Route::post('/request', [TrialCodeController::class, 'request'])->name('request');
    Route::post('/validate', [TrialCodeController::class, 'validate'])->name('validate');
    Route::post('/resend', [TrialCodeController::class, 'resend'])->name('resend');
});

// Admin Trial Code Routes
Route::prefix('admin/trial-codes')->name('admin.trial-codes.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [Admin\TrialCodeController::class, 'index'])->name('index');
    Route::get('/{trialCode}', [Admin\TrialCodeController::class, 'show'])->name('show');
    Route::post('/store', [Admin\TrialCodeController::class, 'store'])->name('store');
    Route::post('/{trialCode}/extend', [Admin\TrialCodeController::class, 'extend'])->name('extend');
    Route::post('/{trialCode}/revoke', [Admin\TrialCodeController::class, 'revoke'])->name('revoke');
    Route::post('/{trialCode}/resend', [Admin\TrialCodeController::class, 'resend'])->name('resend');
    Route::post('/expire-old', [Admin\TrialCodeController::class, 'expireOld'])->name('expire-old');
});

// Admin Twilio Settings Routes
Route::prefix('admin/twilio-settings')->name('admin.twilio-settings.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [Admin\TwilioSettingsController::class, 'index'])->name('index');
    Route::post('/', [Admin\TwilioSettingsController::class, 'update'])->name('update');
    Route::post('/test-connection', [Admin\TwilioSettingsController::class, 'testConnection'])->name('test-connection');
    Route::post('/send-test-sms', [Admin\TwilioSettingsController::class, 'sendTestSms'])->name('send-test-sms');
});
```

### 3.7 API Endpoints (Optional - for future mobile app)

```php
// API Routes
Route::prefix('api/v1/trial-code')->group(function () {
    Route::post('/request', [TrialCodeApiController::class, 'request']);
    Route::post('/validate', [TrialCodeApiController::class, 'validate']);
});
```

---

## Part 4: Implementation Checklist

### Phase 1: Database & Model ✓ COMPLETE
- [x] Create migration for `trial_codes` table
- [x] Create `TrialCode` model with relationships
- [x] Create `TrialCodeService` for business logic
- [x] Create `SystemSetting` model for Twilio configuration
- [x] Create migration for `system_settings` table
- [ ] Run migrations

### Phase 2: Trial Code Request Flow (IN PROGRESS)
- [x] Create `TrialCodeController`
- [ ] Create trial code modal component
- [ ] Implement modal on landing page
- [x] Implement code generation logic
- [x] Set up email notification (in TrialCodeService)
- [x] Set up SMS notification via Twilio (in TrialCodeService)

### Phase 3: Registration Integration
- [ ] Add trial code field to registration form
- [ ] Update `AuthController` to validate trial code
- [ ] Apply trial subscription on valid code
- [ ] Mark code as redeemed

### Phase 4: Admin Features ✓ COMPLETE
- [x] Create `Admin\TrialCodeController`
- [x] Create `Admin\TwilioSettingsController`
- [x] Create admin trial codes list view
- [ ] Create admin trial codes detail view (show.blade.php)
- [x] Create admin Twilio settings view
- [x] Implement extension logic (in service)
- [x] Implement revoke logic (in service)
- [x] Add routes to web.php
- [x] Add to admin navigation

### Phase 5: Landing/Pricing Updates
- [ ] Update "Get Trial Code" button to trigger modal
- [ ] Update Free plan button on pricing page
- [ ] Test complete user flow

### Phase 6: Testing & Polish
- [ ] Test trial code request flow
- [ ] Test registration with trial code
- [ ] Test admin extension (up to 3 times)
- [ ] Test duplicate email prevention
- [ ] Test code expiration
- [ ] Test Twilio SMS integration

---

## Part 5: UI/UX Specifications

### Trial Code Request Page

**URL:** `/trial-code/request`

**Form Fields:**
| Field | Type | Required | Validation |
|-------|------|----------|------------|
| First Name | text | Yes | max:100 |
| Last Name | text | Yes | max:100 |
| Email | email | Yes | unique check, max:255 |
| Phone | tel | No | phone format |
| Organization | text | No | max:255 |
| Delivery Method | radio | Yes | email, sms |

**Design Notes:**
- Match existing registration page style
- Show benefits sidebar (like registration)
- Clear explanation of trial (14 days, no credit card)

### Registration Page Updates

**New Field (shown only for Free Trial plan):**
| Field | Type | Required | Validation |
|-------|------|----------|------------|
| Trial Code | text | Yes (for free) | format: XXXX-XXXX, exists, not expired |

**Placement:** Above the plan selection or below email field

### Admin Trial Codes Page

**Table Columns:**
| Column | Sortable | Filterable |
|--------|----------|------------|
| Code | Yes | Yes (search) |
| Name | Yes | Yes (search) |
| Email | Yes | Yes (search) |
| Status | Yes | Yes (dropdown) |
| Created | Yes | Yes (date range) |
| Redeemed | Yes | Yes (date range) |
| Expires | Yes | Yes (date range) |
| Extensions | Yes | No |
| Actions | No | No |

**Action Buttons:**
- View Details (always)
- Extend Trial (if redeemed, not expired, extensions < 3)
- Revoke Code (if not redeemed)

---

## Part 6: Twilio Management System

### 6.1 Overview

The Twilio Management page allows administrators to configure SMS delivery for trial codes without needing direct access to environment files.

**Admin URL:** `/admin/twilio-settings`

### 6.2 Database Schema

Uses the generic `system_settings` table:

```sql
CREATE TABLE system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group` VARCHAR(50) NOT NULL,      -- e.g., 'twilio'
    `key` VARCHAR(100) NOT NULL,        -- e.g., 'account_sid'
    value TEXT NULL,
    is_encrypted BOOLEAN DEFAULT FALSE,
    type VARCHAR(20) DEFAULT 'string',  -- string, boolean, integer, json
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_group_key (`group`, `key`)
);
```

### 6.3 Twilio Settings Stored

| Key | Type | Encrypted | Description |
|-----|------|-----------|-------------|
| `account_sid` | string | Yes | Twilio Account SID |
| `auth_token` | string | Yes | Twilio Auth Token |
| `from_number` | string | No | Twilio Phone Number (E.164 format) |
| `is_enabled` | boolean | No | Enable/disable SMS sending |
| `test_mode` | boolean | No | Test mode flag |

### 6.4 Features

1. **Secure Key Storage** - Account SID and Auth Token encrypted using Laravel's Crypt
2. **Test Connection** - Verify credentials by fetching account info from Twilio
3. **Send Test SMS** - Send a test message to verify full functionality
4. **Environment Sync** - Updates `.env` file for compatibility with `config/services.php`

### 6.5 Key Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Admin/TwilioSettingsController.php` | Admin controller |
| `app/Models/SystemSetting.php` | Generic settings model |
| `resources/views/admin/twilio-settings/index.blade.php` | Admin UI (to create) |
| `database/migrations/2026_01_19_210000_create_system_settings_table.php` | Migration |

### 6.6 Controller Methods

```php
class TwilioSettingsController extends Controller
{
    public function index();           // Display settings page
    public function update(Request $request);  // Save settings
    public function testConnection(Request $request);  // Test API connection
    public function sendTestSms(Request $request);     // Send test SMS
}
```

### 6.7 Integration with TrialCodeService

The `TrialCodeService::sendSms()` method reads Twilio credentials from:
1. First checks `config('services.twilio.*')` (from .env)
2. Can be modified to use `SystemSetting::getGroup('twilio')` for database settings

---

## Appendix A: Code Generation Algorithm

```php
/**
 * Generate a unique trial code
 * Format: XXXX-XXXX
 * Characters: A-Z, 2-9 (excluding 0, 1, I, L, O)
 */
public function generateCode(): string
{
    $characters = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $maxAttempts = 10;

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // Format as XXXX-XXXX
        $formattedCode = substr($code, 0, 4) . '-' . substr($code, 4, 4);

        // Check uniqueness
        if (!TrialCode::where('code', $formattedCode)->exists()) {
            return $formattedCode;
        }
    }

    throw new \Exception('Unable to generate unique trial code');
}
```

---

## Appendix B: Email Template

**Subject:** Your My Voting Software Trial Code

**Body:**
```
Hi {first_name},

Thank you for your interest in My Voting Software!

Your trial code is: {trial_code}

This code gives you 14 days of free access to all features.

To get started:
1. Visit: {registration_url}
2. Create your account
3. Enter your trial code: {trial_code}

Your code expires on: {expiration_date}

Questions? Reply to this email or visit our support page.

Best,
The My Voting Software Team
```

---

## Appendix C: SMS Template

```
My Voting Software Trial Code: {trial_code}

Register at: {short_registration_url}

Expires: {expiration_date}
```

---

## Summary of Created Files

| File | Status | Description |
|------|--------|-------------|
| `database/migrations/2026_01_19_200000_create_trial_codes_table.php` | Created | Trial codes table migration with FK constraints |
| `database/migrations/2026_01_19_210000_create_system_settings_table.php` | Created | System settings table migration |
| `app/Models/TrialCode.php` | Created | Trial code model with user relationships |
| `app/Models/SystemSetting.php` | Created | System settings Eloquent model |
| `app/Services/TrialCodeService.php` | Created | Trial code business logic |
| `app/Http/Controllers/TrialCodeController.php` | Created | User-facing AJAX endpoints |
| `app/Http/Controllers/Admin/TrialCodeController.php` | Created | Admin management with eager loading |
| `app/Http/Controllers/Admin/TwilioSettingsController.php` | Created | Twilio settings management |
| `resources/views/admin/trial-codes/index.blade.php` | Created | Admin view with linked user display |
| `resources/views/admin/twilio-settings/index.blade.php` | Created | Admin Twilio settings view |
| `database/seeders/TrialCodeSeeder.php` | Created | Sample data with proper FK references |
| `routes/web.php` | Updated | Added trial code and Twilio routes |
| `resources/views/layouts/app.blade.php` | Updated | Uses `<x-sidebar />` component |
| `resources/views/components/sidebar.blade.php` | Updated | Added Trial Codes and Twilio links |
| `public/css/sidebar.css` | Created | Sidebar styles (BEM naming) |
| `public/js/sidebar.js` | Created | Sidebar JavaScript functionality |

## Database Relationships Implemented

```
trial_codes.user_id      -> users.id (redeemed user account)
trial_codes.extended_by  -> users.id (admin who extended)
trial_codes.parent_code_id -> trial_codes.id (extension chains)
```

## Admin View Features

The Trial Codes admin page (`/admin/trial-codes`) displays:
- **Code** - Trial code with delivery method
- **Requester** - Original requester info (name, email, phone, organization)
- **Linked User** - Actual user from `users` table with avatar and link to user edit page
- **Status** - Badge with redemption date
- **Expires** - Expiration date with days remaining
- **Extensions** - Count and admin who extended

### User Data Consistency

The system maintains consistency between trial codes and users:

1. **Before Redemption**: Trial codes have `user_id = NULL` and only display requester info
2. **After Redemption**: `user_id` links to the registered user in the `users` table
3. **Admin View**: Shows BOTH requester info AND the actual linked user from `users` table

**Sample Data Verification (via TrialCodeSeeder):**
```sql
-- Users linked to trial codes (user_id is NOT NULL)
SELECT tc.code, tc.user_id, u.first_name, u.email
FROM trial_codes tc
LEFT JOIN users u ON tc.user_id = u.id
WHERE tc.user_id IS NOT NULL;

-- Result should show matching data:
-- TRL8-K9M2 | 6 | Sarah | sarah.johnson@example.com
-- XYZ4-ABCD | 7 | Mike  | mike.chen@example.com
-- MAX2-DEFG | 6 | Sarah | sarah.johnson@example.com
```

**Key Points:**
- Only **3 of 8** sample trial codes are linked to users (status = 'redeemed')
- The `/admin/users` page shows **ALL** users (7 total)
- The `/admin/trial-codes` page shows all codes, with "Linked User" column for redeemed codes
- Unredeemed codes display "Not registered" in the Linked User column

## Remaining Tasks

- [ ] Create trial code modal component for landing page
- [ ] Update landing page to trigger modal on "Get Trial Code" button
- [ ] Update registration form with trial code field
- [ ] Create trial codes show.blade.php (detail view)

---

## Changelog

### Version 1.3 (January 19, 2026)
- Updated layout references to use new `<x-sidebar />` component
- Added sidebar component files to documentation
- Sidebar now uses external CSS/JS files with BEM naming

### Version 1.2 (January 19, 2026)
- Fixed TrialCodeSeeder to ensure requester data matches linked user data for redeemed codes
- Added detailed documentation on user data consistency between tables
- Added SQL verification queries for data integrity
- Clarified that only redeemed codes have linked users

### Version 1.1 (January 19, 2026)
- Initial implementation of trial code system
- Created all database migrations and models
- Implemented admin views with eager loading
- Added Twilio management interface

---

*Document Version: 1.3*
*Last Updated: January 19, 2026*
*Author: Claude Code Assistant*
