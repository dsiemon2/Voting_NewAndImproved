@extends('layouts.app')

@section('content')
<div class="page-title d-flex justify-between align-center">
    <span><i class="fas fa-sms"></i> Twilio SMS Settings</span>
</div>

<!-- Status Card -->
@if($hasCredentials)
<div class="card" style="background: linear-gradient(135deg, #f22f46 0%, #c4122e 100%); color: white; margin-bottom: 20px;">
    <div class="d-flex justify-between align-center">
        <div>
            <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Twilio Status</div>
            <div style="font-size: 20px; font-weight: bold;">
                {{ $settings['is_enabled'] ? 'Enabled' : 'Configured but Disabled' }}
            </div>
            <div style="font-size: 14px; opacity: 0.9;">
                {{ $settings['test_mode'] ? 'Test Mode' : 'Production Mode' }}
            </div>
        </div>
        <div style="font-size: 48px; opacity: 0.3;">
            <i class="fas fa-{{ $settings['is_enabled'] ? 'check-circle' : 'pause-circle' }}"></i>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> <strong>Twilio Not Configured</strong> - Enter your Twilio credentials below to enable SMS delivery for trial codes.
</div>
@endif

<!-- How It Works -->
<div class="card" style="background: #fef3c7; border: 1px solid #fcd34d; margin-bottom: 20px;">
    <div class="d-flex gap-4" style="align-items: flex-start;">
        <div style="flex: 1; padding: 10px; border-right: 1px solid #fcd34d;">
            <div style="font-weight: 600; color: #92400e; margin-bottom: 5px;">
                <i class="fas fa-key"></i> Get Credentials
            </div>
            <div style="font-size: 13px; color: #78350f;">
                <a href="https://console.twilio.com" target="_blank" style="color: #92400e;">Log in to Twilio Console</a> to get your Account SID and Auth Token.
            </div>
        </div>
        <div style="flex: 1; padding: 10px; border-right: 1px solid #fcd34d;">
            <div style="font-weight: 600; color: #92400e; margin-bottom: 5px;">
                <i class="fas fa-phone"></i> Get Phone Number
            </div>
            <div style="font-size: 13px; color: #78350f;">
                Purchase a phone number in your <a href="https://console.twilio.com/us1/develop/phone-numbers/manage/incoming" target="_blank" style="color: #92400e;">Twilio Console</a>.
            </div>
        </div>
        <div style="flex: 1; padding: 10px;">
            <div style="font-weight: 600; color: #92400e; margin-bottom: 5px;">
                <i class="fas fa-flask"></i> Test First
            </div>
            <div style="font-size: 13px; color: #78350f;">
                Start with Test Mode enabled, then send a test SMS before going live.
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="gap: 20px;">
    <!-- Configuration Card -->
    <div class="card">
        <div class="card-header"><i class="fas fa-cog"></i> Twilio Configuration</div>
        <form method="POST" action="{{ route('admin.twilio-settings.update') }}">
            @csrf
            <div style="padding: 20px;">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-id-card"></i> Account SID
                        @if(!empty($settings['account_sid']))
                        <span class="badge badge-success" style="margin-left: 5px; font-size: 10px;">Configured</span>
                        @endif
                    </label>
                    <input type="text" name="account_sid" class="form-control"
                           value="{{ $settings['account_sid_masked'] ?? '' }}"
                           placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                    <small class="text-muted">Found in your Twilio Console Dashboard</small>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-key"></i> Auth Token
                        @if(!empty($settings['auth_token']))
                        <span class="badge badge-success" style="margin-left: 5px; font-size: 10px;">Set</span>
                        @endif
                    </label>
                    <input type="password" name="auth_token" class="form-control"
                           placeholder="{{ !empty($settings['auth_token']) ? '••••••••••••••••' : 'Enter your Auth Token' }}">
                    <small class="text-muted">Keep this secret - it's stored encrypted</small>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-phone-alt"></i> From Phone Number
                    </label>
                    <input type="tel" name="from_number" class="form-control"
                           value="{{ $settings['from_number'] ?? '' }}"
                           placeholder="+1234567890">
                    <small class="text-muted">Your Twilio phone number in E.164 format (e.g., +15551234567)</small>
                </div>

                <hr style="margin: 20px 0;">

                <div class="d-flex gap-4">
                    <label class="d-flex align-center gap-2" style="cursor: pointer;">
                        <input type="checkbox" name="is_enabled" value="1"
                               {{ ($settings['is_enabled'] ?? false) ? 'checked' : '' }}>
                        <span><i class="fas fa-power-off"></i> Enable SMS Delivery</span>
                    </label>

                    <label class="d-flex align-center gap-2" style="cursor: pointer;">
                        <input type="checkbox" name="test_mode" value="1"
                               {{ ($settings['test_mode'] ?? true) ? 'checked' : '' }}>
                        <span><i class="fas fa-flask"></i> Test Mode</span>
                    </label>
                </div>
            </div>

            <div style="padding: 15px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Test Card -->
    <div class="card">
        <div class="card-header"><i class="fas fa-vial"></i> Test Connection</div>
        <div style="padding: 20px;">
            <p style="margin-bottom: 20px; color: #6b7280;">
                Test your Twilio configuration to ensure everything is set up correctly.
            </p>

            <!-- Test Connection -->
            <div style="margin-bottom: 30px;">
                <h4 style="margin-bottom: 10px;"><i class="fas fa-plug"></i> API Connection</h4>
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 10px;">
                    Verify your Account SID and Auth Token are correct.
                </p>
                <button type="button" class="btn btn-secondary" id="testConnectionBtn" {{ !$hasCredentials ? 'disabled' : '' }}>
                    <i class="fas fa-plug"></i> Test Connection
                </button>
                <div id="connectionResult" style="margin-top: 10px;"></div>
            </div>

            <!-- Test SMS -->
            <div>
                <h4 style="margin-bottom: 10px;"><i class="fas fa-sms"></i> Send Test SMS</h4>
                <p style="font-size: 13px; color: #6b7280; margin-bottom: 10px;">
                    Send a test message to verify SMS delivery works.
                </p>
                <div class="form-group">
                    <input type="tel" id="testPhone" class="form-control" placeholder="+1234567890"
                           style="margin-bottom: 10px;">
                </div>
                <button type="button" class="btn btn-primary" id="sendTestSmsBtn" {{ !$hasCredentials ? 'disabled' : '' }}>
                    <i class="fas fa-paper-plane"></i> Send Test SMS
                </button>
                <div id="smsResult" style="margin-top: 10px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Info Cards -->
<div class="grid grid-2" style="margin-top: 20px;">
    <div class="card">
        <div class="card-header"><i class="fas fa-dollar-sign"></i> Twilio Pricing</div>
        <ul style="list-style: none; padding: 15px; margin: 0;">
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <strong>US Outbound SMS:</strong> ~$0.0079 per message
            </li>
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <strong>US Phone Number:</strong> ~$1.15/month
            </li>
            <li style="padding: 8px 0;">
                <strong>Free Trial:</strong> $15 credit for testing
            </li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header"><i class="fas fa-info-circle"></i> Important Notes</div>
        <ul style="list-style: none; padding: 15px; margin: 0;">
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <i class="fas fa-shield-alt text-success"></i> Credentials are stored encrypted in the database
            </li>
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <i class="fas fa-envelope text-info"></i> If SMS fails, system falls back to email
            </li>
            <li style="padding: 8px 0;">
                <i class="fas fa-sync text-warning"></i> Settings also update .env for compatibility
            </li>
        </ul>
    </div>
</div>

<style>
.grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
}

.text-muted {
    color: #6b7280;
    font-size: 12px;
}

.text-success {
    color: #10b981;
}

.text-info {
    color: #06b6d4;
}

.text-warning {
    color: #f59e0b;
}

input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
    padding: 10px 15px;
    border-radius: 6px;
}

.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
    padding: 10px 15px;
    border-radius: 6px;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Test Connection
    document.getElementById('testConnectionBtn').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('connectionResult');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        resultDiv.innerHTML = '';

        fetch('{{ route("admin.twilio-settings.test-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert-success"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert-danger"><i class="fas fa-times-circle"></i> ' + data.error + '</div>';
            }
        })
        .catch(err => {
            resultDiv.innerHTML = '<div class="alert-danger"><i class="fas fa-times-circle"></i> Connection error</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection';
        });
    });

    // Send Test SMS
    document.getElementById('sendTestSmsBtn').addEventListener('click', function() {
        const btn = this;
        const phone = document.getElementById('testPhone').value.trim();
        const resultDiv = document.getElementById('smsResult');

        if (!phone) {
            resultDiv.innerHTML = '<div class="alert-danger"><i class="fas fa-exclamation-circle"></i> Please enter a phone number</div>';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        resultDiv.innerHTML = '';

        fetch('{{ route("admin.twilio-settings.send-test-sms") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ phone: phone })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert-success"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="alert-danger"><i class="fas fa-times-circle"></i> ' + data.error + '</div>';
            }
        })
        .catch(err => {
            resultDiv.innerHTML = '<div class="alert-danger"><i class="fas fa-times-circle"></i> Error sending SMS</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Test SMS';
        });
    });
});
</script>
@endpush
