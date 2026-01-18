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
        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('system_prompt');
            $table->string('personality')->default('professional'); // professional, friendly, concise, creative
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->string('model')->nullable(); // Override default model
            $table->json('capabilities')->nullable(); // List of enabled capabilities
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Create webhooks table
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('secret')->nullable();
            $table->json('events'); // ['vote.created', 'event.created', etc.]
            $table->json('headers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('retry_count')->default(3);
            $table->integer('timeout')->default(30);
            $table->timestamp('last_triggered_at')->nullable();
            $table->string('last_status')->nullable();
            $table->timestamps();
        });

        // Create webhook_logs table for tracking
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->onDelete('cascade');
            $table->string('event');
            $table->json('payload');
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('attempts')->default(1);
            $table->string('status'); // pending, success, failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // Add scheduling columns to events table
        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('voting_starts_at')->nullable()->after('event_date');
            $table->timestamp('voting_ends_at')->nullable()->after('voting_starts_at');
            $table->boolean('auto_publish_results')->default(false)->after('voting_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['voting_starts_at', 'voting_ends_at', 'auto_publish_results']);
        });
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('ai_agents');
    }
};
