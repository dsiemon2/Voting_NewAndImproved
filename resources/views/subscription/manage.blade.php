@extends('layouts.app')

@section('content')
<div class="page-title d-flex justify-between align-center">
    <span><i class="fas fa-id-card"></i> My Subscription</span>
    <a href="{{ route('subscription.pricing') }}" class="btn btn-primary">
        <i class="fas fa-tags"></i> View All Plans
    </a>
</div>

<!-- Current Plan Summary -->
<div class="grid grid-3" style="margin-bottom: 30px;">
    <div class="card stat-card">
        <div class="stat-icon" style="background: #6366f1;">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $currentPlan->name ?? 'Free' }}</div>
            <div class="stat-label">Current Plan</div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="stat-icon" style="background: #10b981;">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-content">
            @if($subscription)
                @if($subscription->isTrialing())
                    <div class="stat-value">{{ $subscription->trial_ends_at->diffForHumans() }}</div>
                    <div class="stat-label">Trial Ends</div>
                @elseif($subscription->isCanceled())
                    <div class="stat-value">{{ $subscription->current_period_end?->format('M d') ?? 'N/A' }}</div>
                    <div class="stat-label">Access Until</div>
                @else
                    <div class="stat-value">{{ $subscription->current_period_end?->format('M d') ?? 'N/A' }}</div>
                    <div class="stat-label">Next Billing</div>
                @endif
            @else
                <div class="stat-value">N/A</div>
                <div class="stat-label">Billing Date</div>
            @endif
        </div>
    </div>

    <div class="card stat-card">
        <div class="stat-icon" style="background: #f59e0b;">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $currentPlan->getFormattedPrice() ?? 'Free' }}</div>
            <div class="stat-label">{{ $currentPlan && $currentPlan->price > 0 ? 'Per Month' : '' }}</div>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <!-- Subscription Details -->
    <div class="card">
        <div class="card-header"><i class="fas fa-file-invoice"></i> Subscription Details</div>

        @if($subscription)
        <table class="table" style="margin: 0;">
            <tr>
                <td style="color: #6b7280;">Status</td>
                <td>
                    <span class="badge {{ $subscription->getStatusBadgeClass() }}">
                        {{ ucfirst($subscription->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="color: #6b7280;">Plan</td>
                <td><strong>{{ $currentPlan->name }}</strong></td>
            </tr>
            <tr>
                <td style="color: #6b7280;">Price</td>
                <td>{{ $currentPlan->getFormattedPrice() }}/{{ $currentPlan->billing_period }}</td>
            </tr>
            @if($subscription->trial_ends_at && $subscription->isTrialing())
            <tr>
                <td style="color: #6b7280;">Trial Ends</td>
                <td>{{ $subscription->trial_ends_at->format('M d, Y') }}</td>
            </tr>
            @endif
            @if($subscription->current_period_end)
            <tr>
                <td style="color: #6b7280;">{{ $subscription->isCanceled() ? 'Access Until' : 'Current Period Ends' }}</td>
                <td>{{ $subscription->current_period_end->format('M d, Y') }}</td>
            </tr>
            @endif
            @if($subscription->payment_method_brand)
            <tr>
                <td style="color: #6b7280;">Payment Method</td>
                <td>
                    <i class="fab fa-cc-{{ strtolower($subscription->payment_method_brand) }}"></i>
                    {{ ucfirst($subscription->payment_method_brand) }} ending in {{ $subscription->payment_method_last4 }}
                </td>
            </tr>
            @endif
            @if($subscription->canceled_at)
            <tr>
                <td style="color: #6b7280;">Canceled On</td>
                <td>{{ $subscription->canceled_at->format('M d, Y') }}</td>
            </tr>
            @endif
        </table>

        <div style="padding: 20px; border-top: 1px solid #e5e7eb;">
            @if($subscription->isCanceled())
                <button class="btn btn-success resume-btn">
                    <i class="fas fa-redo"></i> Resume Subscription
                </button>
            @elseif($subscription->isActive())
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary billing-portal-btn">
                        <i class="fas fa-credit-card"></i> Manage Billing
                    </button>
                    <button class="btn btn-danger cancel-btn">
                        <i class="fas fa-times"></i> Cancel Subscription
                    </button>
                </div>
            @endif
        </div>
        @else
        <div style="padding: 40px; text-align: center; color: #6b7280;">
            <i class="fas fa-info-circle" style="font-size: 48px; margin-bottom: 15px; color: #d1d5db;"></i>
            <p>You're currently on the Free plan.</p>
            <a href="{{ route('subscription.pricing') }}" class="btn btn-primary">
                <i class="fas fa-arrow-up"></i> Upgrade Your Plan
            </a>
        </div>
        @endif
    </div>

    <!-- Usage Summary -->
    <div class="card">
        <div class="card-header"><i class="fas fa-chart-pie"></i> Usage Summary</div>

        <div style="padding: 20px;">
            <!-- Active Events -->
            <div style="margin-bottom: 25px;">
                <div class="d-flex justify-between align-center" style="margin-bottom: 8px;">
                    <span style="font-weight: 600;">Active Events</span>
                    <span>
                        {{ $activeEvents }} /
                        {{ $currentPlan && $currentPlan->max_events == -1 ? 'Unlimited' : ($currentPlan->max_events ?? 1) }}
                    </span>
                </div>
                @if($currentPlan && $currentPlan->max_events != -1)
                @php
                    $eventsPercent = min(100, ($activeEvents / max(1, $currentPlan->max_events)) * 100);
                @endphp
                <div class="progress-bar">
                    <div class="progress-fill {{ $eventsPercent >= 90 ? 'danger' : ($eventsPercent >= 70 ? 'warning' : '') }}"
                         style="width: {{ $eventsPercent }}%"></div>
                </div>
                @else
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 10%; background: #10b981;"></div>
                </div>
                @endif
            </div>

            <!-- Entries Per Event Limit -->
            <div style="margin-bottom: 25px;">
                <div class="d-flex justify-between align-center" style="margin-bottom: 8px;">
                    <span style="font-weight: 600;">Max Entries Per Event</span>
                    <span>
                        {{ $currentPlan && $currentPlan->max_entries_per_event == -1 ? 'Unlimited' : ($currentPlan->max_entries_per_event ?? 20) }}
                    </span>
                </div>
            </div>

            <!-- Features List -->
            <div style="border-top: 1px solid #e5e7eb; padding-top: 20px;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; text-transform: uppercase; color: #6b7280;">
                    <i class="fas fa-check-circle"></i> Included Features
                </h4>
                <div class="feature-list">
                    @if($currentPlan)
                        @if($currentPlan->has_basic_voting || $currentPlan->has_all_voting_types)
                        <span class="feature-tag">
                            <i class="fas fa-check"></i> {{ $currentPlan->has_all_voting_types ? 'All Voting Types' : 'Basic Voting' }}
                        </span>
                        @endif
                        @if($currentPlan->has_realtime_results)
                        <span class="feature-tag"><i class="fas fa-check"></i> Real-time Results</span>
                        @endif
                        @if($currentPlan->has_custom_templates)
                        <span class="feature-tag"><i class="fas fa-check"></i> Custom Templates</span>
                        @endif
                        @if($currentPlan->has_pdf_ballots)
                        <span class="feature-tag"><i class="fas fa-check"></i> PDF Ballots</span>
                        @endif
                        @if($currentPlan->has_excel_import)
                        <span class="feature-tag"><i class="fas fa-check"></i> Excel Import</span>
                        @endif
                        @if($currentPlan->has_judging_panels)
                        <span class="feature-tag"><i class="fas fa-check"></i> Judging Panels</span>
                        @endif
                        @if($currentPlan->has_advanced_analytics)
                        <span class="feature-tag"><i class="fas fa-check"></i> Advanced Analytics</span>
                        @endif
                        @if($currentPlan->has_api_access)
                        <span class="feature-tag"><i class="fas fa-check"></i> API Access</span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Plan Comparison / Upgrade Options -->
@if($currentPlan && $currentPlan->code !== 'premium')
<div class="card" style="margin-top: 20px;">
    <div class="card-header"><i class="fas fa-arrow-up"></i> Upgrade Your Plan</div>
    <div class="upgrade-options">
        @foreach($plans->where('price', '>', $currentPlan->price ?? 0) as $plan)
        <div class="upgrade-option">
            <div class="upgrade-info">
                <h4>{{ $plan->name }}</h4>
                <p>{{ $plan->description }}</p>
            </div>
            <div class="upgrade-price">
                <span class="price">{{ $plan->getFormattedPrice() }}</span>
                <span class="period">/mo</span>
            </div>
            <div class="upgrade-action">
                @if($plan->code === 'premium')
                <a href="mailto:sales@example.com" class="btn btn-secondary">Contact Sales</a>
                @else
                <button class="btn btn-primary subscribe-btn" data-plan-id="{{ $plan->id }}">
                    Upgrade
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<style>
.stat-card {
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

.stat-label {
    font-size: 13px;
    color: #6b7280;
}

.progress-bar {
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #10b981;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-fill.warning {
    background: #f59e0b;
}

.progress-fill.danger {
    background: #ef4444;
}

.feature-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.feature-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #ecfdf5;
    color: #059669;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.feature-tag i {
    font-size: 10px;
}

.upgrade-options {
    padding: 20px;
}

.upgrade-option {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}

.upgrade-option:hover {
    border-color: #0d7a3e;
    background: #f8fafc;
}

.upgrade-option:last-child {
    margin-bottom: 0;
}

.upgrade-info {
    flex: 1;
}

.upgrade-info h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
    color: #1f2937;
}

.upgrade-info p {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
}

.upgrade-price {
    text-align: right;
}

.upgrade-price .price {
    font-size: 24px;
    font-weight: 700;
    color: #0d6e38;
}

.upgrade-price .period {
    font-size: 14px;
    color: #6b7280;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Subscribe/Upgrade
    document.querySelectorAll('.subscribe-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const planId = this.dataset.planId;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/subscription/subscribe/${planId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.checkout_url) {
                    window.location.href = data.checkout_url;
                } else {
                    alert(data.message || 'Failed to process');
                    this.disabled = false;
                    this.innerHTML = 'Upgrade';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error processing request');
                this.disabled = false;
                this.innerHTML = 'Upgrade';
            });
        });
    });

    // Cancel subscription
    document.querySelectorAll('.cancel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to cancel your subscription? You will still have access until the end of your current billing period.')) {
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Canceling...';

            fetch('/subscription/cancel', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Failed to cancel');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-times"></i> Cancel Subscription';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error canceling subscription');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-times"></i> Cancel Subscription';
            });
        });
    });

    // Resume subscription
    document.querySelectorAll('.resume-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resuming...';

            fetch('/subscription/resume', {
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
                    alert(data.message || 'Failed to resume');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-redo"></i> Resume Subscription';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error resuming subscription');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-redo"></i> Resume Subscription';
            });
        });
    });

    // Billing portal
    document.querySelectorAll('.billing-portal-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch('/subscription/billing-portal', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.portal_url) {
                    window.location.href = data.portal_url;
                } else {
                    alert(data.message || 'Failed to open billing portal');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-credit-card"></i> Manage Billing';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error opening billing portal');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-credit-card"></i> Manage Billing';
            });
        });
    });
});
</script>
@endpush
