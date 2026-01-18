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
        Schema::create('voting_place_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_type_id')->constrained('voting_types')->onDelete('cascade');
            $table->tinyInteger('place');
            $table->decimal('points', 10, 2);
            $table->string('label', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->string('icon', 50)->nullable();
            $table->timestamps();

            $table->unique(['voting_type_id', 'place']);
        });

        // Seed place configs for Standard 3-2-1
        $ranked321Id = DB::table('voting_types')->where('code', 'ranked_321')->value('id');
        DB::table('voting_place_configs')->insert([
            ['voting_type_id' => $ranked321Id, 'place' => 1, 'points' => 3, 'label' => '1st Place', 'color' => '#FFD700', 'icon' => 'fa-trophy', 'created_at' => now(), 'updated_at' => now()],
            ['voting_type_id' => $ranked321Id, 'place' => 2, 'points' => 2, 'label' => '2nd Place', 'color' => '#C0C0C0', 'icon' => 'fa-medal', 'created_at' => now(), 'updated_at' => now()],
            ['voting_type_id' => $ranked321Id, 'place' => 3, 'points' => 1, 'label' => '3rd Place', 'color' => '#CD7F32', 'icon' => 'fa-award', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed place configs for Extended 5-4-3-2-1
        $ranked54321Id = DB::table('voting_types')->where('code', 'ranked_54321')->value('id');
        DB::table('voting_place_configs')->insert([
            ['voting_type_id' => $ranked54321Id, 'place' => 1, 'points' => 5, 'label' => '1st Place', 'color' => '#FFD700', 'icon' => 'fa-trophy', 'created_at' => now(), 'updated_at' => now()],
            ['voting_type_id' => $ranked54321Id, 'place' => 2, 'points' => 4, 'label' => '2nd Place', 'color' => '#C0C0C0', 'icon' => 'fa-medal', 'created_at' => now(), 'updated_at' => now()],
            ['voting_type_id' => $ranked54321Id, 'place' => 3, 'points' => 3, 'label' => '3rd Place', 'color' => '#CD7F32', 'icon' => 'fa-award', 'created_at' => now(), 'updated_at' => now()],
            ['voting_type_id' => $ranked54321Id, 'place' => 4, 'points' => 2, 'label' => '4th Place', 'color' => '#4A90A4', 'icon' => 'fa-star', 'created_at' => now(), 'updated_at' => now()],
            ['voting_type_id' => $ranked54321Id, 'place' => 5, 'points' => 1, 'label' => '5th Place', 'color' => '#6B7280', 'icon' => 'fa-star-half', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed place configs for Top-Heavy 5-3-1
        $ranked531Id = DB::table('voting_types')->where('code', 'ranked_531')->value('id');
        DB::table('voting_place_configs')->insert([
            ['voting_type_id' => $ranked531Id, 'place' => 1, 'points' => 5, 'label' => '1st Place', 'color' => '#FFD700', 'icon' => 'fa-trophy', 'created_at' => now(), 'updated_at' => now()],
            ['voting_type_id' => $ranked531Id, 'place' => 2, 'points' => 3, 'label' => '2nd Place', 'color' => '#C0C0C0', 'icon' => 'fa-medal', 'created_at' => now(), 'updated_at' => now()],
            ['voting_type_id' => $ranked531Id, 'place' => 3, 'points' => 1, 'label' => '3rd Place', 'color' => '#CD7F32', 'icon' => 'fa-award', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_place_configs');
    }
};
