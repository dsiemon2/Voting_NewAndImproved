@extends('layouts.app')

@section('content')
<div class="page-title d-flex justify-between align-center">
    <span><i class="fas fa-shield-alt"></i> Account Settings</span>
</div>

<!-- Account Tabs -->
<div class="card" style="margin-bottom: 20px;">
    <div class="account-tabs">
        <button class="account-tab active" data-tab="security">
            <i class="fas fa-lock"></i> Login & Security
        </button>
        <button class="account-tab" data-tab="payment">
            <i class="fas fa-credit-card"></i> Payment Options
        </button>
        <button class="account-tab" data-tab="notifications">
            <i class="fas fa-bell"></i> Notifications
        </button>
        <button class="account-tab" data-tab="devices">
            <i class="fas fa-laptop"></i> Your Devices
        </button>
    </div>
</div>

<!-- Login & Security Tab -->
<div class="tab-content active" id="security-tab">
    <div class="grid grid-2">
        <div class="card">
            <div class="card-header"><i class="fas fa-user"></i> Personal Information</div>
            <div class="info-row">
                <div class="info-content">
                    <div class="info-label">Name</div>
                    <div class="info-value" id="displayName">{{ $user->full_name }}</div>
                </div>
                <button class="btn btn-sm btn-secondary" onclick="showEditModal('name')">
                    <i class="fas fa-pencil-alt"></i> Edit
                </button>
            </div>
            <div class="info-row">
                <div class="info-content">
                    <div class="info-label">Email</div>
                    <div class="info-value" id="displayEmail">{{ $user->email }}</div>
                </div>
                <button class="btn btn-sm btn-secondary" onclick="showEditModal('email')">
                    <i class="fas fa-pencil-alt"></i> Edit
                </button>
            </div>
            <div class="info-row">
                <div class="info-content">
                    <div class="info-label">Phone</div>
                    <div class="info-value" id="displayPhone">{{ $user->phone ?? 'Not set' }}</div>
                </div>
                <button class="btn btn-sm btn-secondary" onclick="showEditModal('phone')">
                    <i class="fas fa-pencil-alt"></i> Edit
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-key"></i> Password & Security</div>
            <div class="info-row">
                <div class="info-content">
                    <div class="info-label">Password</div>
                    <div class="info-value text-muted">Last changed: Never</div>
                </div>
                <button class="btn btn-sm btn-warning" onclick="showEditModal('password')">
                    <i class="fas fa-key"></i> Change
                </button>
            </div>
            <div class="info-row">
                <div class="info-content">
                    <div class="info-label">Two-Factor Authentication</div>
                    <div class="info-value"><span class="badge badge-secondary">Not Enabled</span></div>
                </div>
                <button class="btn btn-sm btn-success">
                    <i class="fas fa-shield-alt"></i> Enable
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Options Tab -->
<div class="tab-content" id="payment-tab">
    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <span><i class="fas fa-credit-card"></i> Saved Payment Methods</span>
            <button class="btn btn-sm btn-primary" onclick="showAddPaymentModal()">
                <i class="fas fa-plus"></i> Add Payment Method
            </button>
        </div>
        <div id="paymentMethodsList" style="padding: 20px;">
            <p class="text-muted text-center">Loading...</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fas fa-receipt"></i> Billing History</div>
        <div style="padding: 20px;">
            <p class="text-muted">View your recent transactions and download invoices.</p>
            <a href="{{ route('subscription.manage') }}" class="btn btn-secondary">
                <i class="fas fa-list-ul"></i> View Subscription & Billing
            </a>
        </div>
    </div>
</div>

<!-- Notifications Tab -->
<div class="tab-content" id="notifications-tab">
    <div class="card">
        <div class="card-header"><i class="fas fa-bell"></i> Notification Preferences</div>
        <div style="padding: 20px;">
            <p class="text-muted mb-4">Choose how you want to receive notifications.</p>

            <div class="notification-group">
                <h4><i class="fas fa-calendar-alt"></i> Event Notifications</h4>
                <div class="notification-row">
                    <span>Event Updates</span>
                    <div class="notification-switches">
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="event_updates_email" checked>
                            <i class="fas fa-envelope"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="event_updates_sms">
                            <i class="fas fa-sms"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="event_updates_push" checked>
                            <i class="fas fa-bell"></i>
                        </label>
                    </div>
                </div>
            </div>

            <div class="notification-group">
                <h4><i class="fas fa-vote-yea"></i> Voting Notifications</h4>
                <div class="notification-row">
                    <span>Voting Reminders</span>
                    <div class="notification-switches">
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="voting_reminder_email" checked>
                            <i class="fas fa-envelope"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="voting_reminder_sms" checked>
                            <i class="fas fa-sms"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="voting_reminder_push" checked>
                            <i class="fas fa-bell"></i>
                        </label>
                    </div>
                </div>
            </div>

            <div class="notification-group">
                <h4><i class="fas fa-chart-bar"></i> Results Notifications</h4>
                <div class="notification-row">
                    <span>Results Available</span>
                    <div class="notification-switches">
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="results_available_email" checked>
                            <i class="fas fa-envelope"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="results_available_sms">
                            <i class="fas fa-sms"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="results_available_push" checked>
                            <i class="fas fa-bell"></i>
                        </label>
                    </div>
                </div>
            </div>

            <div class="notification-group">
                <h4><i class="fas fa-credit-card"></i> Billing Notifications</h4>
                <div class="notification-row">
                    <span>Subscription Updates</span>
                    <div class="notification-switches">
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="subscription_email" checked>
                            <i class="fas fa-envelope"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="subscription_sms">
                            <i class="fas fa-sms"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="subscription_push" checked>
                            <i class="fas fa-bell"></i>
                        </label>
                    </div>
                </div>
                <div class="notification-row">
                    <span>Payment Confirmations</span>
                    <div class="notification-switches">
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="payment_email" checked>
                            <i class="fas fa-envelope"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="payment_sms">
                            <i class="fas fa-sms"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="payment_push" checked>
                            <i class="fas fa-bell"></i>
                        </label>
                    </div>
                </div>
            </div>

            <div class="notification-group">
                <h4><i class="fas fa-shield-alt"></i> Security Notifications</h4>
                <div class="notification-row">
                    <span>Security Alerts</span>
                    <div class="notification-switches">
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="security_email" checked>
                            <i class="fas fa-envelope"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="security_sms" checked>
                            <i class="fas fa-sms"></i>
                        </label>
                        <label class="switch-label">
                            <input type="checkbox" class="notification-toggle" data-key="security_push" checked>
                            <i class="fas fa-bell"></i>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Devices Tab -->
<div class="tab-content" id="devices-tab">
    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <span><i class="fas fa-laptop"></i> Active Sessions</span>
            <button class="btn btn-sm btn-danger" onclick="signOutAllDevices()">
                <i class="fas fa-sign-out-alt"></i> Sign Out All Other Devices
            </button>
        </div>
        <div id="devicesList" style="padding: 20px;">
            <div class="device-item current">
                <div class="device-info">
                    <i class="fas fa-laptop device-icon"></i>
                    <div>
                        <div class="device-name">Current Device</div>
                        <small class="text-muted">This browser session</small>
                    </div>
                </div>
                <span class="badge badge-success">Active Now</span>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 id="editModalTitle">Edit</h4>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <div class="modal-body" id="editModalBody"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
            <button class="btn btn-primary" onclick="saveEdit()">Save Changes</button>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal-overlay" id="addPaymentModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4><i class="fas fa-credit-card"></i> Add Payment Method</h4>
            <button class="modal-close" onclick="closeModal('addPaymentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Card Number</label>
                <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
            </div>
            <div class="form-group">
                <label class="form-label">Cardholder Name</label>
                <input type="text" class="form-control" id="cardHolderName" placeholder="John Doe">
            </div>
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Expiry Date</label>
                    <div class="d-flex gap-2">
                        <select class="form-control" id="expiryMonth">
                            @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                        <select class="form-control" id="expiryYear">
                            @for($i = 0; $i <= 10; $i++)
                            <option value="{{ date('Y') + $i }}">{{ date('Y') + $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">CVV</label>
                    <input type="text" class="form-control" id="cardCvv" placeholder="123" maxlength="4">
                </div>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="setAsDefault" checked>
                    Set as default payment method
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('addPaymentModal')">Cancel</button>
            <button class="btn btn-primary" onclick="addPaymentMethod()">Add Card</button>
        </div>
    </div>
</div>

<style>
.account-tabs {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
}

.account-tab {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
    transition: all 0.2s;
    border-bottom: 3px solid transparent;
}

.account-tab:hover {
    color: #0d6e38;
    background: #f8fafc;
}

.account-tab.active {
    color: #0d6e38;
    border-bottom-color: #0d6e38;
}

.account-tab i {
    margin-right: 8px;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #e5e7eb;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
}

.info-value {
    font-weight: 600;
    color: #1f2937;
}

.notification-group {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.notification-group:last-child {
    border-bottom: none;
}

.notification-group h4 {
    font-size: 15px;
    color: #0d6e38;
    margin-bottom: 15px;
}

.notification-group h4 i {
    margin-right: 8px;
}

.notification-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
}

.notification-switches {
    display: flex;
    gap: 15px;
}

.switch-label {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    color: #6b7280;
}

.switch-label input:checked + i {
    color: #10b981;
}

.device-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 10px;
}

.device-item.current {
    border-color: #0d6e38;
    background: #f0f9ff;
}

.device-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.device-icon {
    font-size: 24px;
    color: #0d6e38;
}

.device-name {
    font-weight: 600;
    color: #1f2937;
}

.payment-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 10px;
}

.payment-card.default {
    border-color: #0d6e38;
    background: #f0f9ff;
}

.card-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.card-icon {
    font-size: 28px;
}

/* Modal styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h4 {
    margin: 0;
    color: #0d6e38;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let currentEditField = '';

document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('.account-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.account-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.dataset.tab + '-tab').classList.add('active');
        });
    });

    // Load account data
    loadAccountData();

    // Notification toggle handlers
    document.querySelectorAll('.notification-toggle').forEach(toggle => {
        toggle.addEventListener('change', updateNotifications);
    });

    // Card number formatting
    document.getElementById('cardNumber')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(.{4})/g, '$1 ').trim();
        e.target.value = value;
    });
});

async function loadAccountData() {
    try {
        const res = await fetch('/account/data', {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        if (data.success) {
            // Update display
            document.getElementById('displayName').textContent = data.profile.first_name + ' ' + data.profile.last_name;
            document.getElementById('displayEmail').textContent = data.profile.email;
            document.getElementById('displayPhone').textContent = data.profile.phone || 'Not set';

            // Update payment methods
            renderPaymentMethods(data.paymentMethods || []);

            // Update notifications
            if (data.notificationPrefs) {
                Object.keys(data.notificationPrefs).forEach(key => {
                    const toggle = document.querySelector(`[data-key="${key}"]`);
                    if (toggle) toggle.checked = data.notificationPrefs[key];
                });
            }

            // Update devices
            renderDevices(data.devices || []);
        }
    } catch (err) {
        console.error('Error loading account data:', err);
    }
}

function renderPaymentMethods(methods) {
    const container = document.getElementById('paymentMethodsList');
    if (methods.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No payment methods saved yet.</p>';
        return;
    }
    container.innerHTML = methods.map(m => `
        <div class="payment-card ${m.is_default ? 'default' : ''}">
            <div class="card-info">
                <i class="fab fa-cc-${getCardBrand(m.card_type)} card-icon"></i>
                <div>
                    <div style="font-weight: 600;">${m.card_type} ending in ${m.card_last4}</div>
                    <small class="text-muted">Expires ${String(m.expiry_month).padStart(2, '0')}/${m.expiry_year}</small>
                    ${m.is_default ? '<span class="badge badge-info" style="margin-left: 10px;">Default</span>' : ''}
                </div>
            </div>
            <div>
                ${!m.is_default ? `<button class="btn btn-sm btn-secondary" onclick="setDefaultPayment(${m.id})"><i class="fas fa-star"></i></button>` : ''}
                <button class="btn btn-sm btn-danger" onclick="removePaymentMethod(${m.id})"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    `).join('');
}

function getCardBrand(type) {
    const brands = { 'Visa': 'visa', 'Mastercard': 'mastercard', 'Amex': 'amex', 'Discover': 'discover' };
    return brands[type] || 'credit-card';
}

function renderDevices(devices) {
    const container = document.getElementById('devicesList');
    if (devices.length === 0) {
        container.innerHTML = `
            <div class="device-item current">
                <div class="device-info">
                    <i class="fas fa-laptop device-icon"></i>
                    <div>
                        <div class="device-name">Current Device</div>
                        <small class="text-muted">This browser session</small>
                    </div>
                </div>
                <span class="badge badge-success">Active Now</span>
            </div>`;
        return;
    }
    container.innerHTML = devices.map(d => `
        <div class="device-item ${d.is_current ? 'current' : ''}">
            <div class="device-info">
                <i class="fas fa-${getDeviceIcon(d.device_type)} device-icon"></i>
                <div>
                    <div class="device-name">${d.device_name || 'Unknown Device'}</div>
                    <small class="text-muted">${d.browser || ''} ${d.os_name ? '- ' + d.os_name : ''}</small>
                    <br><small class="text-muted">Last active: ${new Date(d.last_seen_at).toLocaleString()}</small>
                </div>
            </div>
            <div>
                ${d.is_current
                    ? '<span class="badge badge-success">Active Now</span>'
                    : `<button class="btn btn-sm btn-danger" onclick="signOutDevice(${d.id})"><i class="fas fa-sign-out-alt"></i></button>`}
            </div>
        </div>
    `).join('');
}

function getDeviceIcon(type) {
    const icons = { 'desktop': 'laptop', 'mobile': 'mobile-alt', 'tablet': 'tablet-alt' };
    return icons[type] || 'desktop';
}

function showModal(id) {
    document.getElementById(id).style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function showEditModal(field) {
    currentEditField = field;
    const title = document.getElementById('editModalTitle');
    const body = document.getElementById('editModalBody');

    const templates = {
        name: {
            title: 'Edit Name',
            body: `
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" id="editFirstName" value="{{ $user->first_name }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="editLastName" value="{{ $user->last_name }}">
                </div>`
        },
        email: {
            title: 'Change Email',
            body: `
                <div class="form-group">
                    <label class="form-label">New Email</label>
                    <input type="email" class="form-control" id="editValue" value="${document.getElementById('displayEmail').textContent}">
                </div>
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="editPassword" placeholder="Enter current password to confirm">
                </div>`
        },
        phone: {
            title: 'Edit Phone',
            body: `
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="editValue" value="${document.getElementById('displayPhone').textContent !== 'Not set' ? document.getElementById('displayPhone').textContent : ''}">
                </div>`
        },
        password: {
            title: 'Change Password',
            body: `
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="currentPassword">
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword">
                </div>`
        }
    };

    const config = templates[field];
    title.textContent = config.title;
    body.innerHTML = config.body;
    showModal('editModal');
}

async function saveEdit() {
    let endpoint = `/account/${currentEditField}`;
    let body = {};

    if (currentEditField === 'name') {
        body = {
            first_name: document.getElementById('editFirstName').value,
            last_name: document.getElementById('editLastName').value
        };
    } else if (currentEditField === 'email') {
        body = {
            email: document.getElementById('editValue').value,
            password: document.getElementById('editPassword').value
        };
    } else if (currentEditField === 'phone') {
        body = { phone: document.getElementById('editValue').value };
    } else if (currentEditField === 'password') {
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;
        if (newPass !== confirmPass) {
            alert('Passwords do not match');
            return;
        }
        body = {
            current_password: document.getElementById('currentPassword').value,
            new_password: newPass
        };
    }

    try {
        const res = await fetch(endpoint, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(body)
        });
        const data = await res.json();
        if (data.success) {
            closeModal('editModal');
            loadAccountData();
        } else {
            alert('Error: ' + (data.error || 'Failed to save'));
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

function showAddPaymentModal() {
    document.getElementById('cardNumber').value = '';
    document.getElementById('cardHolderName').value = '';
    document.getElementById('cardCvv').value = '';
    document.getElementById('setAsDefault').checked = true;
    showModal('addPaymentModal');
}

async function addPaymentMethod() {
    const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
    const cardHolderName = document.getElementById('cardHolderName').value;
    const expiryMonth = document.getElementById('expiryMonth').value;
    const expiryYear = document.getElementById('expiryYear').value;
    const isDefault = document.getElementById('setAsDefault').checked;

    let cardType = 'Card';
    if (cardNumber.startsWith('4')) cardType = 'Visa';
    else if (/^5[1-5]/.test(cardNumber)) cardType = 'Mastercard';
    else if (/^3[47]/.test(cardNumber)) cardType = 'Amex';
    else if (/^6(?:011|5)/.test(cardNumber)) cardType = 'Discover';

    try {
        const res = await fetch('/account/payment-methods', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                card_type: cardType,
                card_last4: cardNumber.slice(-4),
                card_holder_name: cardHolderName,
                expiry_month: parseInt(expiryMonth),
                expiry_year: parseInt(expiryYear),
                is_default: isDefault
            })
        });
        const data = await res.json();
        if (data.success) {
            closeModal('addPaymentModal');
            loadAccountData();
        } else {
            alert('Error: ' + (data.error || 'Failed to add payment method'));
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

async function setDefaultPayment(id) {
    try {
        const res = await fetch(`/account/payment-methods/${id}/default`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        if (data.success) loadAccountData();
        else alert('Error: ' + (data.error || 'Failed to set default'));
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

async function removePaymentMethod(id) {
    if (!confirm('Remove this payment method?')) return;
    try {
        const res = await fetch(`/account/payment-methods/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        if (data.success) loadAccountData();
        else alert('Error: ' + (data.error || 'Failed to remove'));
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

async function updateNotifications() {
    const prefs = {};
    document.querySelectorAll('.notification-toggle').forEach(toggle => {
        prefs[toggle.dataset.key] = toggle.checked;
    });
    try {
        await fetch('/account/notifications', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(prefs)
        });
    } catch (err) {
        console.error('Error updating notifications:', err);
    }
}

async function signOutDevice(id) {
    if (!confirm('Sign out this device?')) return;
    try {
        const res = await fetch(`/account/devices/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        if (data.success) loadAccountData();
        else alert('Error: ' + (data.error || 'Failed to sign out device'));
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

async function signOutAllDevices() {
    if (!confirm('Sign out of all other devices?')) return;
    try {
        const res = await fetch('/account/devices', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        if (data.success) {
            alert('Signed out of all other devices');
            loadAccountData();
        } else {
            alert('Error: ' + (data.error || 'Failed to sign out devices'));
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
}
</script>
@endpush
