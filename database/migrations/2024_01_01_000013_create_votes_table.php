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
        // Voter weight classes for weighted voting
        Schema::create('voter_weight_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_type_id')->constrained('voting_types')->onDelete('cascade');
            $table->string('name', 100);
            $table->decimal('weight_multiplier', 5, 2)->default(1.00);
            $table->text('description')->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->timestamps();
        });

        // User voter class assignments per event
        Schema::create('user_voter_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('voter_weight_class_id')->constrained('voter_weight_classes')->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['user_id', 'event_id']);
        });

        // Main votes table
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('entry_id')->constrained('entries')->onDelete('cascade');
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');

            // For ranked voting
            $table->tinyInteger('place')->nullable();

            // For rating voting
            $table->decimal('rating', 3, 1)->nullable();

            // Points calculation
            $table->decimal('base_points', 10, 2)->default(0);
            $table->decimal('weight_multiplier', 5, 2)->default(1.00);
            $table->decimal('final_points', 10, 2)->storedAs('base_points * weight_multiplier');

            // For anonymous voting tracking
            $table->string('voter_ip', 45)->nullable();
            $table->string('voter_fingerprint', 255)->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['event_id', 'entry_id']);
            $table->index(['event_id', 'division_id']);
            $table->index(['user_id', 'event_id']);
            $table->index(['event_id', 'created_at']);
        });

        // Vote summary for quick results (denormalized for performance)
        Schema::create('vote_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('entry_id')->constrained('entries')->onDelete('cascade');
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->decimal('total_points', 12, 2)->default(0);
            $table->integer('vote_count')->default(0);
            $table->integer('first_place_count')->default(0);
            $table->integer('second_place_count')->default(0);
            $table->integer('third_place_count')->default(0);
            $table->decimal('average_rating', 4, 2)->nullable();
            $table->integer('ranking')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'entry_id', 'division_id', 'category_id'], 'vote_summary_unique');
            $table->index(['event_id', 'total_points']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_summaries');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('user_voter_classes');
        Schema::dropIfExists('voter_weight_classes');
    }
};
