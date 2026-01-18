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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_template_id')->constrained('event_templates')->onDelete('restrict');
            $table->foreignId('voting_type_id')->nullable()->constrained('voting_types')->onDelete('set null');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->date('event_date')->nullable();
            $table->string('location', 255)->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'event_date']);
            $table->index('event_template_id');
        });

        // Event module overrides
        Schema::create('event_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->boolean('is_enabled')->default(true);
            $table->string('custom_label', 100)->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'module_id']);
        });

        // Event voting configuration
        Schema::create('event_voting_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('voting_type_id')->constrained('voting_types')->onDelete('restrict');
            $table->integer('max_votes_per_user')->nullable();
            $table->integer('max_votes_per_entry')->default(1);
            $table->boolean('allow_self_voting')->default(false);
            $table->timestamp('voting_starts_at')->nullable();
            $table->timestamp('voting_ends_at')->nullable();
            $table->boolean('show_live_results')->default(false);
            $table->boolean('show_vote_counts')->default(true);
            $table->boolean('show_percentages')->default(true);
            $table->json('place_overrides')->nullable();
            $table->timestamps();

            $table->unique('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_voting_configs');
        Schema::dropIfExists('event_modules');
        Schema::dropIfExists('events');
    }
};
