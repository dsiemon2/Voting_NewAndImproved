@extends('layouts.app')

@section('content')
<div class="page-title d-flex justify-between align-center">
    <span><i class="fas fa-credit-card"></i> Payment Gateways</span>
</div>

@php
    $activeGateway = $gateways->first(fn($g) => $g->is_enabled);
@endphp

<!-- Active Provider Summary -->
@if($activeGateway)
<div class="card" style="background: linear-gradient(135deg, #635BFF 0%, #7c3aed 100%); color: white; margin-bottom: 20px;">
    <div class="d-flex justify-between align-center">
        <div>
            <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Active Payment Provider</div>
            <div style="font-size: 20px; font-weight: bold;">{{ $providers[$activeGateway->provider]['name'] ?? ucfirst($activeGateway->provider) }}</div>
            <div style="font-size: 14px; opacity: 0.9;">
                {{ $activeGateway->test_mode ? 'Test Mode' : 'Live Mode' }}
                @if($activeGateway->ach_enabled) | ACH Enabled @endif
            </div>
        </div>
        <div style="font-size: 48px; opacity: 0.3;">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> <strong>No Payment Provider Active</strong> - Configure and enable a payment gateway below to accept payments.
</div>
@endif

<!-- How It Works -->
<div class="card" style="background: #f0f9ff; border: 1px solid #bae6fd; margin-bottom: 20px;">
    <div class="d-flex gap-4" style="align-items: flex-start;">
        <div style="flex: 1; padding: 10px; border-right: 1px solid #bae6fd;">
            <div style="font-weight: 600; color: #0369a1; margin-bottom: 5px;">
                <i class="fas fa-key"></i> Configure Keys
            </div>
            <div style="font-size: 13px; color: #475569;">
                Enter your API keys for each payment provider. Keys are stored securely in the database.
            </div>
        </div>
        <div style="flex: 1; padding: 10px; border-right: 1px solid #bae6fd;">
            <div style="font-weight: 600; color: #059669; margin-bottom: 5px;">
                <i class="fas fa-toggle-on"></i> Enable Provider
            </div>
            <div style="font-size: 13px; color: #475569;">
                <strong>Only one provider can be active at a time.</strong> Enabling one disables the others.
            </div>
        </div>
        <div style="flex: 1; padding: 10px;">
            <div style="font-weight: 600; color: #7c3aed; margin-bottom: 5px;">
                <i class="fas fa-flask"></i> Test Mode
            </div>
            <div style="font-size: 13px; color: #475569;">
                Toggle test mode to safely test payments without processing real transactions.
            </div>
        </div>
    </div>
</div>

<!-- Provider Cards Grid -->
<div class="grid grid-2" style="margin-bottom: 20px;">
    @foreach($providers as $providerCode => $provider)
    @php
        $gateway = $gateways[$providerCode] ?? null;
        $isEnabled = $gateway?->is_enabled ?? false;
        $hasKeys = !empty($gateway?->publishable_key);
    @endphp
    <div class="card provider-card" data-provider="{{ $providerCode }}"
         style="border: 2px solid {{ $isEnabled ? '#10b981' : '#e5e7eb' }}; position: relative;">

        <!-- Enabled Badge -->
        @if($isEnabled)
        <div style="position: absolute; top: 15px; left: 15px;">
            <span class="badge badge-success"><i class="fas fa-check"></i> Active</span>
        </div>
        @endif

        <!-- Provider Header -->
        <div style="padding: 15px 0; text-align: center; border-bottom: 1px solid #e5e7eb; margin-bottom: 15px;">
            <div style="font-size: 48px; margin-bottom: 10px; color: {{ $provider['color'] }};">
                <i class="{{ $provider['icon'] }}"></i>
            </div>
            <h3 style="margin: 0; color: #1f2937;">{{ $provider['name'] }}</h3>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #6b7280;">{{ $provider['description'] }}</p>
            <div style="margin-top: 8px;">
                <span class="badge" style="background: {{ $provider['color'] }}15; color: {{ $provider['color'] }};">
                    Fee: {{ $provider['fee'] }}
                </span>
            </div>
        </div>

        <!-- Features -->
        <div style="margin-bottom: 15px;">
            <label class="form-label" style="font-size: 12px; text-transform: uppercase; color: #6b7280;">
                <i class="fas fa-check-circle"></i> Supported Methods
            </label>
            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                @foreach($provider['features'] as $feature)
                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px;">{{ $feature }}</span>
                @endforeach
            </div>
        </div>

        <!-- API Keys Section -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-eye"></i>
                @if($providerCode === 'stripe')
                    Publishable Key
                @elseif($providerCode === 'braintree')
                    Public Key
                @elseif($providerCode === 'square')
                    Application ID
                @else
                    API Login ID
                @endif
                @if($hasKeys)
                    <span class="badge badge-success" style="margin-left: 5px; font-size: 10px;">Configured</span>
                @endif
            </label>
            <input type="text" class="form-control publishable-key-input"
                   data-provider="{{ $providerCode }}"
                   value="{{ $gateway?->publishable_key ?? '' }}"
                   placeholder="Enter {{ $providerCode === 'stripe' ? 'pk_live_...' : ($providerCode === 'authorize' ? 'API Login ID' : 'Public key...') }}">
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-key"></i>
                @if($providerCode === 'stripe')
                    Secret Key
                @elseif($providerCode === 'braintree')
                    Private Key
                @elseif($providerCode === 'square')
                    Access Token
                @else
                    Transaction Key
                @endif
                @if($gateway && !empty($gateway->secret_key))
                    <span class="badge badge-success" style="margin-left: 5px; font-size: 10px;">Set</span>
                @endif
            </label>
            <input type="password" class="form-control secret-key-input"
                   data-provider="{{ $providerCode }}"
                   placeholder="{{ $gateway && !empty($gateway->secret_key) ? '••••••••••••••••' : 'Enter secret key...' }}">
        </div>

        @if($providerCode === 'braintree' || $providerCode === 'square')
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-store"></i> Merchant ID
            </label>
            <input type="text" class="form-control merchant-id-input"
                   data-provider="{{ $providerCode }}"
                   value="{{ $gateway?->merchant_id ?? '' }}"
                   placeholder="Enter merchant ID...">
        </div>
        @endif

        <!-- Advanced Settings -->
        <details style="margin-top: 10px;">
            <summary style="cursor: pointer; font-size: 13px; color: #6b7280; margin-bottom: 10px;">
                <i class="fas fa-cog"></i> Advanced Settings
            </summary>

            <div class="form-group" style="margin-bottom: 10px;">
                <label class="form-label" style="font-size: 12px;">
                    <i class="fas fa-shield-alt"></i> Webhook Secret
                </label>
                <input type="password" class="form-control webhook-secret-input"
                       data-provider="{{ $providerCode }}"
                       placeholder="{{ $gateway && !empty($gateway->webhook_secret) ? '••••••••••••••••' : 'Enter webhook secret...' }}">
            </div>

            <div class="d-flex gap-4" style="margin-top: 15px;">
                <label class="d-flex align-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" class="test-mode-toggle" data-provider="{{ $providerCode }}"
                           {{ ($gateway?->test_mode ?? true) ? 'checked' : '' }}>
                    <span style="font-size: 13px;"><i class="fas fa-flask"></i> Test/Sandbox Mode</span>
                </label>

                @if($providerCode === 'stripe')
                <label class="d-flex align-center gap-2" style="cursor: pointer;">
                    <input type="checkbox" class="ach-toggle" data-provider="{{ $providerCode }}"
                           {{ ($gateway?->ach_enabled ?? false) ? 'checked' : '' }}>
                    <span style="font-size: 13px;"><i class="fas fa-university"></i> ACH Bank Transfers</span>
                </label>
                @endif
            </div>
        </details>

        <!-- Action Buttons -->
        <div class="d-flex gap-2" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
            <button class="btn btn-primary btn-sm save-gateway flex-1" data-provider="{{ $providerCode }}">
                <i class="fas fa-save"></i> Save
            </button>
            <button class="btn btn-secondary btn-sm test-gateway flex-1" data-provider="{{ $providerCode }}"
                    {{ !$hasKeys ? 'disabled' : '' }}>
                <i class="fas fa-plug"></i> Test
            </button>
            @if($isEnabled)
            <button class="btn btn-danger btn-sm disable-gateway flex-1" data-provider="{{ $providerCode }}">
                <i class="fas fa-times"></i> Disable
            </button>
            @else
            <button class="btn btn-success btn-sm enable-gateway flex-1" data-provider="{{ $providerCode }}"
                    {{ !$hasKeys ? 'disabled' : '' }}>
                <i class="fas fa-check"></i> Enable
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>

<!-- Comparison Table -->
<div class="card">
    <div class="card-header"><i class="fas fa-balance-scale"></i> Provider Comparison</div>
    <table class="table" style="margin: 0;">
        <thead>
            <tr>
                <th>Provider</th>
                <th>Processing Fee</th>
                <th>Credit Cards</th>
                <th>ACH/eCheck</th>
                <th>PayPal</th>
                <th>Apple Pay</th>
                <th>Google Pay</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><i class="fab fa-stripe" style="color: #635BFF;"></i> Stripe</td>
                <td>2.9% + 30c</td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-times text-danger"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
            </tr>
            <tr>
                <td><i class="fas fa-credit-card" style="color: #003087;"></i> Braintree</td>
                <td>2.59% + 49c</td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-times text-danger"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
            </tr>
            <tr>
                <td><i class="fas fa-square" style="color: #006AFF;"></i> Square</td>
                <td>2.6% + 10c</td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-times text-danger"></i></td>
                <td><i class="fas fa-times text-danger"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
            </tr>
            <tr>
                <td><i class="fas fa-university" style="color: #1C3D6E;"></i> Authorize.net</td>
                <td>2.9% + 30c</td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-times text-danger"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
                <td><i class="fas fa-check text-success"></i></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Info Cards -->
<div class="grid grid-2" style="margin-top: 20px;">
    <div class="card">
        <div class="card-header"><i class="fas fa-link"></i> Get API Keys</div>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <a href="https://dashboard.stripe.com/apikeys" target="_blank" style="color: #635BFF; text-decoration: none;">
                    <i class="fab fa-stripe"></i> Stripe Dashboard - API Keys
                </a>
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <a href="https://www.braintreegateway.com/login" target="_blank" style="color: #003087; text-decoration: none;">
                    <i class="fas fa-credit-card"></i> Braintree Control Panel
                </a>
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <a href="https://squareup.com/dashboard/apps/my-applications" target="_blank" style="color: #006AFF; text-decoration: none;">
                    <i class="fas fa-square"></i> Square Developer Dashboard
                </a>
            </li>
            <li style="padding: 10px 0;">
                <a href="https://www.authorize.net/about-us/contact.html" target="_blank" style="color: #1C3D6E; text-decoration: none;">
                    <i class="fas fa-university"></i> Authorize.net Merchant Interface
                </a>
            </li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header"><i class="fas fa-info-circle"></i> Important Notes</div>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <strong><i class="fas fa-shield-alt text-success"></i> Secure Storage</strong> - API keys are stored encrypted in the database, not in environment files.
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <strong><i class="fas fa-flask text-warning"></i> Test Mode</strong> - Always test with sandbox/test keys before going live.
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <strong><i class="fas fa-exchange-alt text-primary"></i> Single Provider</strong> - Only one payment provider can be active at a time.
            </li>
            <li style="padding: 10px 0;">
                <strong><i class="fas fa-sync text-info"></i> Webhooks</strong> - Configure webhooks in your provider dashboard for payment notifications.
            </li>
        </ul>
    </div>
</div>

<style>
.provider-card {
    transition: all 0.3s ease;
}

.provider-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.flex-1 {
    flex: 1;
}

.text-success {
    color: #10b981 !important;
}

.text-danger {
    color: #ef4444 !important;
}

.text-warning {
    color: #f59e0b !important;
}

.text-primary {
    color: #0d7a3e !important;
}

.text-info {
    color: #06b6d4 !important;
}

input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Save Gateway Configuration
    document.querySelectorAll('.save-gateway').forEach(btn => {
        btn.addEventListener('click', function() {
            const provider = this.dataset.provider;
            const card = this.closest('.provider-card');

            const publishableKey = card.querySelector('.publishable-key-input').value.trim();
            const secretKey = card.querySelector('.secret-key-input').value.trim();
            const webhookSecret = card.querySelector('.webhook-secret-input')?.value.trim();
            const merchantId = card.querySelector('.merchant-id-input')?.value.trim();
            const testMode = card.querySelector('.test-mode-toggle')?.checked ?? true;
            const achEnabled = card.querySelector('.ach-toggle')?.checked ?? false;

            if (!publishableKey) {
                alert('Please enter the publishable/public key');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const data = {
                publishable_key: publishableKey,
                test_mode: testMode,
                ach_enabled: achEnabled,
            };

            if (secretKey) data.secret_key = secretKey;
            if (webhookSecret) data.webhook_secret = webhookSecret;
            if (merchantId) data.merchant_id = merchantId;

            fetch(`/admin/payment-processing/${provider}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.innerHTML = '<i class="fas fa-check"></i> Saved!';
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-success');

                    // Clear secret key input and update placeholder
                    card.querySelector('.secret-key-input').value = '';
                    card.querySelector('.secret-key-input').placeholder = '••••••••••••••••';

                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert(data.message || 'Failed to save configuration');
                    this.innerHTML = '<i class="fas fa-save"></i> Save';
                }
            })
            .catch(err => {
                alert('Error saving configuration');
                console.error(err);
                this.innerHTML = '<i class="fas fa-save"></i> Save';
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });

    // Test Gateway Connection
    document.querySelectorAll('.test-gateway').forEach(btn => {
        btn.addEventListener('click', function() {
            const provider = this.dataset.provider;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

            fetch(`/admin/payment-processing/${provider}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.innerHTML = '<i class="fas fa-check"></i> Success!';
                    this.classList.remove('btn-secondary');
                    this.classList.add('btn-success');
                    alert(data.message);
                } else {
                    this.innerHTML = '<i class="fas fa-times"></i> Failed';
                    this.classList.remove('btn-secondary');
                    this.classList.add('btn-danger');
                    alert('Test failed: ' + (data.message || 'Unknown error'));
                }

                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-plug"></i> Test';
                    this.classList.remove('btn-success', 'btn-danger');
                    this.classList.add('btn-secondary');
                }, 3000);
            })
            .catch(err => {
                alert('Error testing connection');
                console.error(err);
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });

    // Enable Gateway
    document.querySelectorAll('.enable-gateway').forEach(btn => {
        btn.addEventListener('click', function() {
            const provider = this.dataset.provider;

            if (!confirm(`Enable ${provider.charAt(0).toUpperCase() + provider.slice(1)} as your payment provider? This will disable any other active provider.`)) {
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/admin/payment-processing/${provider}/enable`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to enable provider');
                }
            })
            .catch(err => {
                alert('Error enabling provider');
                console.error(err);
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-check"></i> Enable';
            });
        });
    });

    // Disable Gateway
    document.querySelectorAll('.disable-gateway').forEach(btn => {
        btn.addEventListener('click', function() {
            const provider = this.dataset.provider;

            if (!confirm(`Disable ${provider.charAt(0).toUpperCase() + provider.slice(1)}? You will need to enable another provider to accept payments.`)) {
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/admin/payment-processing/${provider}/disable`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to disable provider');
                }
            })
            .catch(err => {
                alert('Error disabling provider');
                console.error(err);
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });
});
</script>
@endpush
