@extends('layouts.app')

@section('content')
<div class="pricing-page">
    <div class="pricing-header text-center">
        <h1 class="page-title"><i class="fas fa-tags"></i> Choose Your Plan</h1>
        <p style="color: #6b7280; max-width: 600px; margin: 0 auto 30px;">
            Select the perfect plan for your event management needs. All plans include a 14-day free trial.
        </p>
    </div>

    @if($currentPlan)
    <div class="card" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; margin-bottom: 30px;">
        <div class="d-flex justify-between align-center">
            <div>
                <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Current Plan</div>
                <div style="font-size: 24px; font-weight: bold;">{{ $currentPlan->name }}</div>
                @if($subscription && $subscription->isTrialing())
                <div style="font-size: 14px; opacity: 0.9;">
                    Trial ends {{ $subscription->trial_ends_at->format('M d, Y') }}
                </div>
                @elseif($subscription && $subscription->current_period_end)
                <div style="font-size: 14px; opacity: 0.9;">
                    {{ $subscription->isCanceled() ? 'Access until' : 'Renews' }} {{ $subscription->current_period_end->format('M d, Y') }}
                </div>
                @endif
            </div>
            <div style="text-align: right;">
                <div style="font-size: 36px; font-weight: bold;">{{ $currentPlan->getFormattedPrice() }}</div>
                @if($currentPlan->price > 0)
                <div style="font-size: 14px; opacity: 0.8;">/month</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="pricing-grid">
        @foreach($plans as $plan)
        <div class="pricing-card {{ $plan->is_popular ? 'popular' : '' }} {{ $currentPlan && $currentPlan->id === $plan->id ? 'current' : '' }}">
            @if($plan->is_popular)
            <div class="popular-badge">Most Popular</div>
            @endif

            @if($currentPlan && $currentPlan->id === $plan->id)
            <div class="current-badge">Current Plan</div>
            @endif

            <div class="plan-header">
                <h3 class="plan-name">{{ $plan->name }}</h3>
                <div class="plan-price">
                    <span class="price">{{ $plan->price == 0 ? 'Free' : '$' . number_format($plan->price, 2) }}</span>
                    @if($plan->price > 0)
                    <span class="period">/mo</span>
                    @endif
                </div>
                <p class="plan-description">{{ $plan->description }}</p>
            </div>

            <div class="plan-features">
                <ul>
                    <!-- Event & Entry Limits -->
                    <li>
                        <i class="fas fa-check"></i>
                        <span>{{ $plan->max_events == -1 ? 'Unlimited' : $plan->max_events }} Active Event{{ $plan->max_events != 1 ? 's' : '' }}</span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span>{{ $plan->max_entries_per_event == -1 ? 'Unlimited' : 'Up to ' . $plan->max_entries_per_event }} Entries</span>
                    </li>

                    <!-- Voting Features -->
                    @if($plan->has_all_voting_types)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>All Voting Types</span>
                    </li>
                    @elseif($plan->has_basic_voting)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Basic Voting Types</span>
                    </li>
                    @endif

                    @if($plan->has_realtime_results)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Real-time Results</span>
                    </li>
                    @endif

                    @if($plan->has_custom_templates)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Custom Templates</span>
                    </li>
                    @endif

                    @if($plan->has_pdf_ballots)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>PDF Ballots</span>
                    </li>
                    @endif

                    @if($plan->has_excel_import)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Excel Import</span>
                    </li>
                    @endif

                    @if($plan->has_judging_panels)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Judging Panels</span>
                    </li>
                    @endif

                    @if($plan->has_advanced_analytics)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Advanced Analytics</span>
                    </li>
                    @endif

                    @if($plan->has_white_label)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>White-label Options</span>
                    </li>
                    @endif

                    @if($plan->has_api_access)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>API Access</span>
                    </li>
                    @endif

                    @if($plan->has_custom_integrations)
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Custom Integrations</span>
                    </li>
                    @endif

                    <!-- Support Level -->
                    <li>
                        <i class="fas fa-headset"></i>
                        <span>
                            @switch($plan->support_level)
                                @case('dedicated')
                                    Dedicated Support
                                    @break
                                @case('priority')
                                    Priority Support
                                    @break
                                @case('email')
                                    Email Support
                                    @break
                                @default
                                    Community Support
                            @endswitch
                        </span>
                    </li>
                </ul>
            </div>

            <div class="plan-action">
                @if(!auth()->check())
                    <a href="{{ route('register', ['plan' => $plan->code]) }}" class="btn btn-{{ $plan->cta_style }} btn-block">
                        {{ $plan->cta_text }}
                    </a>
                @elseif($currentPlan && $currentPlan->id === $plan->id)
                    @if($subscription && $subscription->isCanceled())
                        <button class="btn btn-success btn-block resume-subscription" data-plan-id="{{ $plan->id }}">
                            <i class="fas fa-redo"></i> Resume Subscription
                        </button>
                    @else
                        <button class="btn btn-secondary btn-block" disabled>
                            <i class="fas fa-check"></i> Current Plan
                        </button>
                    @endif
                @elseif($plan->price == 0)
                    <button class="btn btn-secondary btn-block" disabled>
                        Already Included
                    </button>
                @elseif($plan->code === 'premium')
                    <a href="mailto:sales@example.com" class="btn btn-{{ $plan->cta_style }} btn-block">
                        <i class="fas fa-envelope"></i> Contact Sales
                    </a>
                @else
                    <button class="btn btn-{{ $plan->cta_style }} btn-block subscribe-btn" data-plan-id="{{ $plan->id }}">
                        @if($currentPlan && $currentPlan->price < $plan->price)
                            <i class="fas fa-arrow-up"></i> Upgrade
                        @elseif($currentPlan && $currentPlan->price > $plan->price)
                            <i class="fas fa-arrow-down"></i> Downgrade
                        @else
                            {{ $plan->cta_text }}
                        @endif
                    </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- FAQ Section -->
    <div class="faq-section" style="margin-top: 40px;">
        <h2 class="text-center" style="margin-bottom: 30px;"><i class="fas fa-question-circle"></i> Frequently Asked Questions</h2>
        <div class="grid grid-2">
            <div class="card">
                <h4><i class="fas fa-credit-card"></i> What payment methods do you accept?</h4>
                <p style="color: #6b7280; margin: 0;">We accept all major credit cards (Visa, MasterCard, American Express) through our secure Stripe payment processing.</p>
            </div>
            <div class="card">
                <h4><i class="fas fa-clock"></i> What happens during the free trial?</h4>
                <p style="color: #6b7280; margin: 0;">You get full access to all features of your selected plan for 14 days. No charge until the trial ends.</p>
            </div>
            <div class="card">
                <h4><i class="fas fa-sync-alt"></i> Can I change plans later?</h4>
                <p style="color: #6b7280; margin: 0;">Yes! You can upgrade or downgrade at any time. Changes take effect immediately and we'll prorate the difference.</p>
            </div>
            <div class="card">
                <h4><i class="fas fa-times-circle"></i> How do I cancel?</h4>
                <p style="color: #6b7280; margin: 0;">Cancel anytime from your account settings. You'll keep access until the end of your current billing period.</p>
            </div>
        </div>
    </div>
</div>

<style>
.pricing-page {
    max-width: 1200px;
    margin: 0 auto;
}

.pricing-header {
    margin-bottom: 30px;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

@media (max-width: 1200px) {
    .pricing-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .pricing-grid {
        grid-template-columns: 1fr;
    }
}

.pricing-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 30px 25px;
    position: relative;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.pricing-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    transform: translateY(-4px);
}

.pricing-card.popular {
    border: 2px solid #f39c12;
    box-shadow: 0 4px 16px rgba(255, 102, 0, 0.2);
}

.pricing-card.current {
    border: 2px solid #10b981;
}

.popular-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f39c12 0%, #ff8533 100%);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.current-badge {
    position: absolute;
    top: -12px;
    right: 15px;
    background: #10b981;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.plan-header {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 20px;
}

.plan-name {
    font-size: 22px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 15px 0;
}

.plan-price {
    margin-bottom: 10px;
}

.plan-price .price {
    font-size: 36px;
    font-weight: 800;
    color: #0d6e38;
}

.plan-price .period {
    font-size: 16px;
    color: #6b7280;
}

.plan-description {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.plan-features {
    flex: 1;
}

.plan-features ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.plan-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    font-size: 14px;
    color: #374151;
}

.plan-features li i {
    color: #10b981;
    width: 16px;
}

.plan-features li i.fa-headset {
    color: #6366f1;
}

.plan-action {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.btn-block {
    display: block;
    width: 100%;
    text-align: center;
}

.faq-section .card {
    padding: 20px;
}

.faq-section h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #1f2937;
}

.faq-section h4 i {
    color: #6366f1;
    margin-right: 8px;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Subscribe to plan
    document.querySelectorAll('.subscribe-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const planId = this.dataset.planId;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

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
                    alert(data.message || 'Failed to start subscription');
                    this.disabled = false;
                    this.innerHTML = 'Subscribe';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error processing subscription');
                this.disabled = false;
                this.innerHTML = 'Subscribe';
            });
        });
    });

    // Resume subscription
    document.querySelectorAll('.resume-subscription').forEach(btn => {
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
                    alert(data.message || 'Failed to resume subscription');
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
});
</script>
@endpush
