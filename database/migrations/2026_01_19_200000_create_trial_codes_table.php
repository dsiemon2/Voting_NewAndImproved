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
        Schema::create('trial_codes', function (Blueprint $table) {
            $table->id();

            // Trial Code (format: XXXX-XXXX)
            $table->string('code', 10)->unique();

            // Requester Info (before registration)
            $table->string('requester_first_name', 100);
            $table->string('requester_last_name', 100);
            $table->string('requester_email', 255);
            $table->string('requester_phone', 20)->nullable();
            $table->string('requester_organization', 255)->nullable();
            $table->enum('delivery_method', ['email', 'sms'])->default('email');

            // Linked User (after registration)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Status
            $table->enum('status', ['pending', 'sent', 'redeemed', 'expired', 'revoked'])->default('pending');

            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('expires_at')->useCurrent();

            // Extension tracking
            $table->unsignedTinyInteger('extension_count')->default(0);
            $table->foreignId('extended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_extended_at')->nullable();
            $table->foreignId('parent_code_id')->nullable()->constrained('trial_codes')->nullOnDelete();

            // Audit
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('requester_email');
            $table->index('requester_phone');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trial_codes');
    }
};
