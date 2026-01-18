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
        // Add soft deletes to votes table
        Schema::table('votes', function (Blueprint $table) {
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('deleted_reason')->nullable();
        });

        // Add deleted_by and deleted_reason to participants
        Schema::table('participants', function (Blueprint $table) {
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('deleted_reason')->nullable();
        });

        // Add deleted_by and deleted_reason to entries
        Schema::table('entries', function (Blueprint $table) {
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('deleted_reason')->nullable();
        });

        // Create deletion history table for audit trail
        Schema::create('deletion_history', function (Blueprint $table) {
            $table->id();
            $table->string('deletable_type'); // Model class name
            $table->unsignedBigInteger('deletable_id'); // Original record ID
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('set null');
            $table->string('item_name'); // Name/identifier of deleted item
            $table->string('item_type'); // Human readable type (Participant, Entry, Vote)
            $table->json('original_data'); // Full snapshot of the deleted record
            $table->json('related_deletions')->nullable(); // Info about cascade deletions
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('deleted_reason')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamps();

            $table->index(['deletable_type', 'deletable_id']);
            $table->index(['event_id', 'deleted_at']);
            $table->index(['deleted_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deletion_history');

        Schema::table('entries', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_by', 'deleted_reason']);
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_by', 'deleted_reason']);
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['deleted_at', 'deleted_by', 'deleted_reason']);
        });
    }
};
