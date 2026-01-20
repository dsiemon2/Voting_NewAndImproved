<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $userRole = Role::firstOrCreate(
            ['name' => 'User'],
            ['description' => 'Regular user', 'is_system' => false]
        );

        $this->user = User::factory()->create([
            'role_id' => $userRole->id,
        ]);

        // Create subscription plans using firstOrCreate to avoid duplicate key errors
        SubscriptionPlan::firstOrCreate(
            ['code' => 'free'],
            [
                'name' => 'Free Trial',
                'description' => 'Free trial plan with basic features',
                'price' => 0,
                'billing_period' => 'monthly',
                'max_events' => 1,
                'max_entries_per_event' => 20,
                'has_basic_voting' => true,
                'has_realtime_results' => true,
                'has_custom_templates' => true,
                'has_pdf_ballots' => true,
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['code' => 'nonprofit'],
            [
                'name' => 'Non-Profit',
                'description' => 'Non-profit plan with additional features',
                'price' => 9.99,
                'billing_period' => 'monthly',
                'max_events' => 3,
                'max_entries_per_event' => 100,
                'has_basic_voting' => true,
                'has_all_voting_types' => true,
                'has_realtime_results' => true,
                'has_custom_templates' => true,
                'has_pdf_ballots' => true,
                'has_excel_import' => true,
                'is_active' => true,
                'display_order' => 2,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['code' => 'professional'],
            [
                'name' => 'Professional',
                'description' => 'Professional plan for growing organizations',
                'price' => 29.99,
                'billing_period' => 'monthly',
                'max_events' => 10,
                'max_entries_per_event' => -1, // Unlimited
                'has_basic_voting' => true,
                'has_all_voting_types' => true,
                'has_realtime_results' => true,
                'has_custom_templates' => true,
                'has_pdf_ballots' => true,
                'has_excel_import' => true,
                'has_judging_panels' => true,
                'has_advanced_analytics' => true,
                'is_active' => true,
                'display_order' => 3,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['code' => 'premium'],
            [
                'name' => 'Premium',
                'description' => 'Premium plan with all features',
                'price' => 59.00,
                'billing_period' => 'monthly',
                'max_events' => -1, // Unlimited
                'max_entries_per_event' => -1, // Unlimited
                'has_basic_voting' => true,
                'has_all_voting_types' => true,
                'has_realtime_results' => true,
                'has_custom_templates' => true,
                'has_pdf_ballots' => true,
                'has_excel_import' => true,
                'has_judging_panels' => true,
                'has_advanced_analytics' => true,
                'has_white_label' => true,
                'has_api_access' => true,
                'has_custom_integrations' => true,
                'is_active' => true,
                'display_order' => 4,
            ]
        );
    }

    /**
     * Test subscription plan can be created
     */
    public function test_subscription_plan_exists(): void
    {
        $this->assertDatabaseHas('subscription_plans', ['code' => 'free']);
        $this->assertDatabaseHas('subscription_plans', ['code' => 'nonprofit']);
        $this->assertDatabaseHas('subscription_plans', ['code' => 'professional']);
        $this->assertDatabaseHas('subscription_plans', ['code' => 'premium']);
    }

    /**
     * Test free plan has correct limits
     */
    public function test_free_plan_has_correct_limits(): void
    {
        $freePlan = SubscriptionPlan::where('code', 'free')->first();

        $this->assertEquals(1, $freePlan->max_events);
        $this->assertEquals(20, $freePlan->max_entries_per_event);
        $this->assertEquals(0, $freePlan->price);
    }

    /**
     * Test premium plan has unlimited resources
     */
    public function test_premium_plan_has_unlimited_resources(): void
    {
        $premiumPlan = SubscriptionPlan::where('code', 'premium')->first();

        $this->assertTrue($premiumPlan->isUnlimitedEvents());
        $this->assertTrue($premiumPlan->isUnlimitedEntries());
    }

    /**
     * Test plan feature checking
     */
    public function test_plan_has_feature(): void
    {
        $freePlan = SubscriptionPlan::where('code', 'free')->first();
        $premiumPlan = SubscriptionPlan::where('code', 'premium')->first();

        $this->assertTrue($freePlan->hasFeature('basic_voting'));
        $this->assertFalse($freePlan->hasFeature('api_access'));

        $this->assertTrue($premiumPlan->hasFeature('api_access'));
        $this->assertTrue($premiumPlan->hasFeature('white_label'));
    }

    /**
     * Test user subscription can be created
     */
    public function test_user_subscription_can_be_created(): void
    {
        $plan = SubscriptionPlan::where('code', 'free')->first();

        $subscription = UserSubscription::create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $this->user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test subscription belongs to user
     */
    public function test_subscription_belongs_to_user(): void
    {
        $plan = SubscriptionPlan::where('code', 'free')->first();

        $subscription = UserSubscription::create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $this->assertEquals($this->user->id, $subscription->user->id);
    }

    /**
     * Test get free plan helper
     */
    public function test_get_free_plan_helper(): void
    {
        $freePlan = SubscriptionPlan::getFreePlan();

        $this->assertNotNull($freePlan);
        $this->assertEquals('free', $freePlan->code);
    }

    /**
     * Test get active plans
     */
    public function test_get_active_plans(): void
    {
        $activePlans = SubscriptionPlan::getActivePlans();

        $this->assertEquals(4, $activePlans->count());
        // Should be ordered by display_order
        $this->assertEquals('free', $activePlans->first()->code);
    }

    /**
     * Test formatted price
     */
    public function test_formatted_price(): void
    {
        $freePlan = SubscriptionPlan::where('code', 'free')->first();
        $proPlan = SubscriptionPlan::where('code', 'professional')->first();

        $this->assertEquals('Free', $freePlan->getFormattedPrice());
        $this->assertEquals('$29.99', $proPlan->getFormattedPrice());
    }

    /**
     * Test pricing page is accessible
     */
    public function test_pricing_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get(route('subscription.pricing'));

        $response->assertStatus(200);
        $response->assertViewIs('subscription.pricing');
    }

    /**
     * Test subscription status tracking
     */
    public function test_subscription_status_tracking(): void
    {
        $plan = SubscriptionPlan::where('code', 'professional')->first();

        // Create active subscription
        $subscription = UserSubscription::create([
            'user_id' => $this->user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $this->assertEquals('active', $subscription->status);

        // Cancel subscription
        $subscription->update(['status' => 'canceled']);
        $this->assertEquals('canceled', $subscription->fresh()->status);
    }

    /**
     * Test plans include expected features
     */
    public function test_plans_have_expected_features(): void
    {
        $freePlan = SubscriptionPlan::where('code', 'free')->first();
        $nonprofitPlan = SubscriptionPlan::where('code', 'nonprofit')->first();
        $professionalPlan = SubscriptionPlan::where('code', 'professional')->first();
        $premiumPlan = SubscriptionPlan::where('code', 'premium')->first();

        // Free plan features
        $this->assertTrue($freePlan->has_basic_voting);
        $this->assertTrue($freePlan->has_realtime_results);
        $this->assertFalse($freePlan->has_all_voting_types ?? false);

        // Non-profit adds excel import
        $this->assertTrue($nonprofitPlan->has_excel_import);
        $this->assertTrue($nonprofitPlan->has_all_voting_types);

        // Professional adds judging panels
        $this->assertTrue($professionalPlan->has_judging_panels);
        $this->assertTrue($professionalPlan->has_advanced_analytics);

        // Premium adds white-label and API
        $this->assertTrue($premiumPlan->has_white_label);
        $this->assertTrue($premiumPlan->has_api_access);
        $this->assertTrue($premiumPlan->has_custom_integrations);
    }

    /**
     * Test subscription plan pricing tiers
     */
    public function test_subscription_plan_pricing_tiers(): void
    {
        $plans = SubscriptionPlan::orderBy('price')->get();

        $this->assertEquals(0, $plans[0]->price);      // Free
        $this->assertEquals(9.99, $plans[1]->price);   // Non-Profit
        $this->assertEquals(29.99, $plans[2]->price);  // Professional
        $this->assertEquals(59.00, $plans[3]->price);  // Premium
    }
}
