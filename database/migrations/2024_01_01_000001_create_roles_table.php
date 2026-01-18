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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Seed default roles
        DB::table('roles')->insert([
            ['name' => 'Administrator', 'description' => 'Full system access', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Member', 'description' => 'Standard member access', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'User', 'description' => 'Basic user access', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Judge', 'description' => 'Judge with weighted voting', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
