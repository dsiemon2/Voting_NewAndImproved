<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old system_settings table with organization fields
        Schema::dropIfExists('system_settings');

        // Create new system_settings table with key-value structure
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index();
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->string('type', 20)->default('string');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');

        // Recreate the old system_settings table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name')->default('VotigoPro');
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
};
