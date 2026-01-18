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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique(); // stripe, braintree, square, authorize
            $table->boolean('is_enabled')->default(false);
            $table->string('publishable_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->boolean('test_mode')->default(true);
            $table->boolean('ach_enabled')->default(false);
            $table->string('webhook_secret')->nullable();
            $table->string('merchant_id')->nullable(); // For Braintree/Square
            $table->text('additional_config')->nullable(); // JSON for provider-specific settings
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
