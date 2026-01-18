<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class SubscriptionController extends Controller
{
    protected ?StripeClient $stripe = null;

    public function __construct()
    {
        $this->initializeStripe();
    }

    protected function initializeStripe(): void
    {
        $gateway = PaymentGateway::where('provider', 'stripe')
            ->where('is_enabled', true)
            ->first();

        if ($gateway && $gateway->secret_key) {
            $this->stripe = new StripeClient($gateway->secret_key);
        }
    }

    /**
     * Show the pricing/plans page
     */
    public function pricing()
    {
        $plans = SubscriptionPlan::getActivePlans();
        $currentPlan = Auth::check() ? Auth::user()->currentPlan() : null;
        $subscription = Auth::check() ? Auth::user()->activeSubscription() : null;

        return view('subscription.pricing', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Show the subscription management page
     */
    public function manage()
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription();
        $currentPlan = $user->currentPlan();
        $plans = SubscriptionPlan::getActivePlans();

        // Get usage stats
        $activeEvents = $user->createdEvents()->where('is_active', true)->count();
        $totalEntries = $user->createdEvents()->withCount('entries')->get()->sum('entries_count');

        return view('subscription.manage', [
            'subscription' => $subscription,
            'currentPlan' => $currentPlan,
            'plans' => $plans,
            'activeEvents' => $activeEvents,
            'totalEntries' => $totalEntries,
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        if (!$this->stripe) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing is not configured. Please contact support.',
            ], 500);
        }

        $user = Auth::user();

        // Check if already subscribed to this plan
        $currentSubscription = $user->activeSubscription();
        if ($currentSubscription && $currentSubscription->subscription_plan_id === $plan->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are already subscribed to this plan.',
            ], 400);
        }

        try {
            // Get or create Stripe customer
            $customerId = $this->getOrCreateStripeCustomer($user);

            // Create checkout session
            $gateway = PaymentGateway::where('provider', 'stripe')->first();

            $sessionParams = [
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $plan->name,
                            'description' => $plan->description,
                        ],
                        'unit_amount' => (int) ($plan->price * 100),
                        'recurring' => [
                            'interval' => $plan->billing_period === 'yearly' ? 'year' : 'month',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscription.pricing'),
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ],
            ];

            // Add trial period for paid plans
            if ($plan->price > 0 && !$currentSubscription) {
                $sessionParams['subscription_data'] = [
                    'trial_period_days' => 14,
                ];
            }

            $session = $this->stripe->checkout->sessions->create($sessionParams);

            return response()->json([
                'success' => true,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle successful subscription
     */
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId || !$this->stripe) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Invalid session or payment not configured.');
        }

        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription', 'customer'],
            ]);

            $user = Auth::user();

            // Cancel any existing subscription
            $existingSubscription = $user->activeSubscription();
            if ($existingSubscription && $existingSubscription->stripe_subscription_id) {
                try {
                    $this->stripe->subscriptions->cancel($existingSubscription->stripe_subscription_id);
                } catch (\Exception $e) {
                    Log::warning('Could not cancel existing subscription: ' . $e->getMessage());
                }
                $existingSubscription->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                ]);
            }

            // Create new subscription record
            $planId = $session->metadata->plan_id ?? null;
            $stripeSubscription = $session->subscription;

            UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $planId,
                'stripe_subscription_id' => $stripeSubscription->id ?? null,
                'stripe_customer_id' => $session->customer->id ?? $session->customer,
                'status' => $stripeSubscription->status ?? 'active',
                'trial_ends_at' => isset($stripeSubscription->trial_end)
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->trial_end)
                    : null,
                'current_period_start' => isset($stripeSubscription->current_period_start)
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start)
                    : now(),
                'current_period_end' => isset($stripeSubscription->current_period_end)
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                    : now()->addMonth(),
            ]);

            return redirect()->route('subscription.manage')
                ->with('success', 'Subscription activated successfully! Welcome to your new plan.');

        } catch (ApiErrorException $e) {
            Log::error('Stripe session retrieval error: ' . $e->getMessage());
            return redirect()->route('subscription.manage')
                ->with('error', 'Could not verify subscription. Please contact support.');
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.',
            ], 400);
        }

        try {
            // Cancel at period end (user keeps access until current period ends)
            if ($subscription->stripe_subscription_id && $this->stripe) {
                $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
                    'cancel_at_period_end' => true,
                ]);
            }

            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription canceled. You will have access until ' .
                    ($subscription->current_period_end?->format('M d, Y') ?? 'the end of your billing period'),
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe cancellation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not cancel subscription. Please try again.',
            ], 500);
        }
    }

    /**
     * Resume a canceled subscription
     */
    public function resume(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()
            ->where('status', 'canceled')
            ->whereNotNull('stripe_subscription_id')
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No canceled subscription found to resume.',
            ], 400);
        }

        try {
            if ($this->stripe) {
                $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
                    'cancel_at_period_end' => false,
                ]);
            }

            $subscription->update([
                'status' => 'active',
                'canceled_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription resumed successfully!',
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe resume error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not resume subscription. Please try again.',
            ], 500);
        }
    }

    /**
     * Create Stripe billing portal session
     */
    public function billingPortal(Request $request)
    {
        if (!$this->stripe) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing is not configured.',
            ], 500);
        }

        $user = Auth::user();
        $subscription = $user->activeSubscription();

        if (!$subscription || !$subscription->stripe_customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'No billing information found.',
            ], 400);
        }

        try {
            $session = $this->stripe->billingPortal->sessions->create([
                'customer' => $subscription->stripe_customer_id,
                'return_url' => route('subscription.manage'),
            ]);

            return response()->json([
                'success' => true,
                'portal_url' => $session->url,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe billing portal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not open billing portal. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle Stripe webhooks
     */
    public function webhook(Request $request)
    {
        $gateway = PaymentGateway::where('provider', 'stripe')->first();

        if (!$gateway || !$gateway->webhook_secret) {
            Log::warning('Stripe webhook received but no webhook secret configured');
            return response()->json(['error' => 'Webhook not configured'], 400);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $gateway->webhook_secret
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $this->handleSubscriptionUpdate($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleSubscriptionUpdate($stripeSubscription): void
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'status' => $stripeSubscription->status,
                'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                'canceled_at' => $stripeSubscription->canceled_at
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->canceled_at)
                    : null,
                'ended_at' => $stripeSubscription->ended_at
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->ended_at)
                    : null,
            ]);
        }
    }

    protected function handlePaymentSucceeded($invoice): void
    {
        // Update subscription status if needed
        if ($invoice->subscription) {
            $subscription = UserSubscription::where('stripe_subscription_id', $invoice->subscription)->first();
            if ($subscription && $subscription->status === 'past_due') {
                $subscription->update(['status' => 'active']);
            }
        }
    }

    protected function handlePaymentFailed($invoice): void
    {
        if ($invoice->subscription) {
            $subscription = UserSubscription::where('stripe_subscription_id', $invoice->subscription)->first();
            if ($subscription) {
                $subscription->update(['status' => 'past_due']);
            }
        }
    }

    protected function getOrCreateStripeCustomer($user): string
    {
        // Check if user has an existing Stripe customer ID
        $existingSubscription = $user->subscriptions()
            ->whereNotNull('stripe_customer_id')
            ->first();

        if ($existingSubscription) {
            return $existingSubscription->stripe_customer_id;
        }

        // Create new customer
        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->full_name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        return $customer->id;
    }

    /**
     * Get Stripe publishable key for frontend
     */
    public function getPublishableKey()
    {
        $gateway = PaymentGateway::where('provider', 'stripe')
            ->where('is_enabled', true)
            ->first();

        return response()->json([
            'publishable_key' => $gateway?->publishable_key,
        ]);
    }
}
