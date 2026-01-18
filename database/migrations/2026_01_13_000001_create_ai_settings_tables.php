<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AI Configuration
        Schema::create('ai_configs', function (Blueprint $table) {
            $table->id();
            $table->string('selected_voice')->default('alloy');
            $table->string('assistant_mode')->default('hybrid'); // ai_only, hybrid
            $table->string('intensity')->default('moderate'); // gentle, moderate, persistent, full
            $table->string('default_model')->default('gpt-4o');
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->integer('max_tokens')->default(4096);
            $table->decimal('speech_speed', 3, 2)->default(1.00);
            $table->boolean('enable_tts')->default(true);
            $table->boolean('content_filter')->default(true);
            $table->boolean('pii_detection')->default(true);
            $table->boolean('transcript_logging')->default(true);
            $table->boolean('record_calls')->default(false);
            $table->text('greeting_message')->nullable();
            $table->string('greeting_voice')->nullable();
            $table->timestamps();
        });

        // AI Languages
        Schema::create('ai_languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('native_name');
            $table->string('flag', 10)->nullable();
            $table->boolean('enabled')->default(true);
            $table->integer('doc_count')->default(0);
            $table->timestamps();
        });

        // Feature Settings
        Schema::create('feature_settings', function (Blueprint $table) {
            $table->id();

            // Core Features
            $table->boolean('public_voting')->default(true);
            $table->boolean('live_results')->default(true);
            $table->boolean('judging_panel')->default(false);
            $table->boolean('import_export')->default(true);
            $table->boolean('pdf_reports')->default(false);
            $table->boolean('categories')->default(true);

            // AI Chat Slider
            $table->boolean('ai_chat_enabled')->default(true);
            $table->boolean('ai_show_on_voting')->default(true);
            $table->boolean('ai_show_on_results')->default(true);
            $table->boolean('ai_show_on_admin')->default(true);
            $table->boolean('ai_show_on_dashboard')->default(true);
            $table->boolean('ai_show_on_landing')->default(false);
            $table->boolean('ai_show_on_mobile')->default(true);

            // AI Capabilities
            $table->boolean('ai_voice_input')->default(true);
            $table->boolean('ai_voice_output')->default(true);
            $table->boolean('ai_charts')->default(true);
            $table->boolean('ai_data_modification')->default(false);

            // AI Appearance
            $table->string('ai_chat_position')->default('bottom-right');
            $table->string('ai_button_color')->default('#1e40af');
            $table->integer('ai_panel_width')->default(380);

            // Notifications
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('notify_email')->default(true);
            $table->boolean('notify_sms')->default(false);
            $table->boolean('notify_push')->default(true);
            $table->boolean('notify_event_reminders')->default(true);
            $table->boolean('notify_voting_updates')->default(true);
            $table->boolean('notify_result_alerts')->default(true);
            $table->boolean('notify_admin_alerts')->default(true);
            $table->boolean('notify_security_alerts')->default(true);
            $table->boolean('notify_promotional')->default(false);

            $table->timestamps();
        });

        // System Settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name')->default('My Voting Software');
            $table->string('organization_email')->nullable();
            $table->string('organization_phone')->nullable();
            $table->text('organization_address')->nullable();
            $table->string('timezone')->default('America/New_York');
            $table->string('date_format')->default('M d, Y');
            $table->string('time_format')->default('h:i A');
            $table->string('primary_color')->default('#1e40af');
            $table->string('accent_color')->default('#ff6600');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->unsignedBigInteger('default_voting_type')->nullable();
            $table->integer('max_votes_per_user')->default(1);
            $table->boolean('allow_vote_changes')->default(false);
            $table->boolean('require_email_verification')->default(false);
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('feature_settings');
        Schema::dropIfExists('ai_languages');
        Schema::dropIfExists('ai_configs');
    }
};
