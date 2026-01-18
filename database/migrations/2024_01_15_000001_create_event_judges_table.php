<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_judges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('vote_weight', 5, 2)->default(1.00); // e.g., 2.00 = vote counts double
            $table->string('title')->nullable(); // e.g., "Head Judge", "Guest Judge"
            $table->text('bio')->nullable(); // Short bio for display
            $table->boolean('is_active')->default(true);
            $table->boolean('can_see_results')->default(true); // Can view results before public
            $table->boolean('can_vote_own_division')->default(false); // Can vote in divisions they're assigned to
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_judges');
    }
};
