<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // free, nonprofit, professional, premium
            $table->string('name');
            $table->string('description');
            $table->decimal('price', 8, 2)->default(0);
            $table->string('billing_period')->default('monthly'); // monthly, yearly
            $table->string('stripe_price_id')->nullable();

            // Limits
            $table->integer('max_events')->default(1); // -1 for unlimited
            $table->integer('max_entries_per_event')->default(20); // -1 for unlimited

            // Features (JSON for flexibility)
            $table->json('features')->nullable();

            // Feature flags
            $table->boolean('has_basic_voting')->default(true);
            $table->boolean('has_all_voting_types')->default(false);
            $table->boolean('has_realtime_results')->default(true);
            $table->boolean('has_custom_templates')->default(false);
            $table->boolean('has_pdf_ballots')->default(true);
            $table->boolean('has_excel_import')->default(false);
            $table->boolean('has_judging_panels')->default(false);
            $table->boolean('has_advanced_analytics')->default(false);
            $table->boolean('has_white_label')->default(false);
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_custom_integrations')->default(false);

            // Support level
            $table->string('support_level')->default('community'); // community, email, priority, dedicated

            // Display
            $table->integer('display_order')->default(0);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('cta_text')->default('Get Started');
            $table->string('cta_style')->default('primary'); // primary, success, warning, secondary

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
