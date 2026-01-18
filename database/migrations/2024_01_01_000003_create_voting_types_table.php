<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voting_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('category', ['ranked', 'approval', 'weighted', 'rating', 'cumulative']);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // Seed default voting types
        DB::table('voting_types')->insert([
            [
                'code' => 'ranked_321',
                'name' => 'Standard Ranked (3-2-1)',
                'description' => 'Classic 3-point, 2-point, 1-point ranking for 3 places',
                'category' => 'ranked',
                'is_system' => true,
                'is_active' => true,
                'settings' => json_encode(['places' => 3]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ranked_54321',
                'name' => 'Extended Ranked (5-4-3-2-1)',
                'description' => 'Five-place ranking system',
                'category' => 'ranked',
                'is_system' => true,
                'is_active' => true,
                'settings' => json_encode(['places' => 5]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ranked_531',
                'name' => 'Top-Heavy (5-3-1)',
                'description' => 'Emphasis on winning position',
                'category' => 'ranked',
                'is_system' => true,
                'is_active' => true,
                'settings' => json_encode(['places' => 3]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'equal_weight',
                'name' => 'Equal Weight',
                'description' => 'All votes count equally (1 point each)',
                'category' => 'approval',
                'is_system' => true,
                'is_active' => true,
                'settings' => json_encode(['points_per_vote' => 1]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'approval_limited',
                'name' => 'Limited Approval (Top 3)',
                'description' => 'Select up to 3 favorites',
                'category' => 'approval',
                'is_system' => true,
                'is_active' => true,
                'settings' => json_encode(['max_selections' => 3, 'points_per_vote' => 1]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'weighted_judged',
                'name' => 'Weighted with Judges',
                'description' => 'Judges votes count more than public',
                'category' => 'weighted',
                'is_system' => true,
                'is_active' => true,
                'settings' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'star_rating',
                'name' => '5-Star Rating',
                'description' => 'Rate entries 1-5 stars',
                'category' => 'rating',
                'is_system' => true,
                'is_active' => true,
                'settings' => json_encode(['min_rating' => 1, 'max_rating' => 5]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_types');
    }
};
