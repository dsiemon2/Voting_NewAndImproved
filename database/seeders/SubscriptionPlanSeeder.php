<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Free Trial Plan
        SubscriptionPlan::updateOrCreate(
            ['code' => 'free'],
            [
                'name' => 'Free Trial',
                'description' => 'Perfect for trying it out',
                'price' => 0,
                'billing_period' => 'monthly',
                'max_events' => 1,
                'max_entries_per_event' => 20,
                'features' => [
                    '1 Active Event',
                    'Up to 20 Entries',
                    'Basic Voting Types',
                    'Real-time Results',
                    'Custom Templates',
                    'PDF Ballots',
                ],
                'has_basic_voting' => true,
                'has_all_voting_types' => false,
                'has_realtime_results' => true,
                'has_custom_templates' => true,
                'has_pdf_ballots' => true,
                'has_excel_import' => false,
                'has_judging_panels' => false,
                'has_advanced_analytics' => false,
                'has_white_label' => false,
                'has_api_access' => false,
                'has_custom_integrations' => false,
                'support_level' => 'community',
                'display_order' => 1,
                'is_popular' => false,
                'is_active' => true,
                'cta_text' => 'Get Started Free',
                'cta_style' => 'secondary',
            ]
        );

        // Non-Profit Plan
        SubscriptionPlan::updateOrCreate(
            ['code' => 'nonprofit'],
            [
                'name' => 'Non-Profit',
                'description' => 'For community organizations',
                'price' => 9.99,
                'billing_period' => 'monthly',
                'max_events' => 3,
                'max_entries_per_event' => 100,
                'features' => [
                    '3 Active Events',
                    'Up to 100 Entries',
                    'All Voting Types',
                    'Excel Import',
                    'PDF Ballots',
                    'Email Support',
                ],
                'has_basic_voting' => true,
                'has_all_voting_types' => true,
                'has_realtime_results' => true,
                'has_custom_templates' => true,
                'has_pdf_ballots' => true,
                'has_excel_import' => true,
                'has_judging_panels' => false,
                'has_advanced_analytics' => false,
                'has_white_label' => false,
                'has_api_access' => false,
                'has_custom_integrations' => false,
                'support_level' => 'email',
                'display_order' => 2,
                'is_popular' => false,
                'is_active' => true,
                'cta_text' => 'Start Free Trial',
                'cta_style' => 'primary',
            ]
        );

        // Professional Plan
        SubscriptionPlan::updateOrCreate(
            ['code' => 'professional'],
            [
                'name' => 'Professional',
                'description' => 'For serious event organizers',
                'price' => 29.99,
                'billing_period' => 'monthly',
                'max_events' => 10,
                'max_entries_per_event' => -1, // Unlimited
                'features' => [
                    '10 Active Events',
                    'Unlimited Entries',
                    'Custom Templates',
                    'Judging Panels',
                    'Advanced Analytics',
                    'Priority Support',
                ],
                'has_basic_voting' => true,
                'has_all_voting_types' => true,
                'has_realtime_results' => true,
                'has_custom_templates' => true,
                'has_pdf_ballots' => true,
                'has_excel_import' => true,
                'has_judging_panels' => true,
                'has_advanced_analytics' => true,
                'has_white_label' => false,
                'has_api_access' => false,
                'has_custom_integrations' => false,
                'support_level' => 'priority',
                'display_order' => 3,
                'is_popular' => true,
                'is_active' => true,
                'cta_text' => 'Start Free Trial',
                'cta_style' => 'warning',
            ]
        );

        // Premium Plan
        SubscriptionPlan::updateOrCreate(
            ['code' => 'premium'],
            [
                'name' => 'Premium',
                'description' => 'For large-scale operations',
                'price' => 59.00,
                'billing_period' => 'monthly',
                'max_events' => -1, // Unlimited
                'max_entries_per_event' => -1, // Unlimited
                'features' => [
                    'Unlimited Events',
                    'Unlimited Everything',
                    'White-label Options',
                    'API Access',
                    'Custom Integrations',
                    'Dedicated Support',
                ],
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
                'support_level' => 'dedicated',
                'display_order' => 4,
                'is_popular' => false,
                'is_active' => true,
                'cta_text' => 'Contact Sales',
                'cta_style' => 'success',
            ]
        );
    }
}
