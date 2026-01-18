<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add phone field to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
        });

        // Create user_payment_methods table
        Schema::create('user_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_default')->default(false);
            $table->string('card_type');
            $table->string('card_last4');
            $table->string('card_holder_name');
            $table->integer('expiry_month');
            $table->integer('expiry_year');
            $table->string('gateway')->nullable();
            $table->string('gateway_customer_id')->nullable();
            $table->string('gateway_payment_method_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        // Create user_notification_preferences table
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Event notifications
            $table->boolean('event_updates_email')->default(true);
            $table->boolean('event_updates_sms')->default(false);
            $table->boolean('event_updates_push')->default(true);

            // Voting notifications
            $table->boolean('voting_reminder_email')->default(true);
            $table->boolean('voting_reminder_sms')->default(true);
            $table->boolean('voting_reminder_push')->default(true);

            // Results notifications
            $table->boolean('results_available_email')->default(true);
            $table->boolean('results_available_sms')->default(false);
            $table->boolean('results_available_push')->default(true);

            // Subscription notifications
            $table->boolean('subscription_email')->default(true);
            $table->boolean('subscription_sms')->default(false);
            $table->boolean('subscription_push')->default(true);

            // Payment notifications
            $table->boolean('payment_email')->default(true);
            $table->boolean('payment_sms')->default(false);
            $table->boolean('payment_push')->default(true);

            // Security notifications
            $table->boolean('security_email')->default(true);
            $table->boolean('security_sms')->default(true);
            $table->boolean('security_push')->default(true);

            $table->timestamps();
        });

        // Create user_devices table
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_name');
            $table->string('device_type')->default('desktop');
            $table->string('os_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('browser')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('user_notification_preferences');
        Schema::dropIfExists('user_payment_methods');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
