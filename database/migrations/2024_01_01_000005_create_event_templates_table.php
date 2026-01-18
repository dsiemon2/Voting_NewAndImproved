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
        Schema::create('event_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('participant_label', 50)->default('Participant');
            $table->string('entry_label', 50)->default('Entry');
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default templates
        DB::table('event_templates')->insert([
            [
                'name' => 'Food Competition',
                'description' => 'Cooking competitions like Soup Cookoff, Bake Off, Chili Contest',
                'icon' => 'fa-utensils',
                'participant_label' => 'Chef',
                'entry_label' => 'Entry',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Photo Contest',
                'description' => 'Photography competitions',
                'icon' => 'fa-camera',
                'participant_label' => 'Photographer',
                'entry_label' => 'Photo',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General Vote',
                'description' => 'Simple voting for any purpose',
                'icon' => 'fa-check-square',
                'participant_label' => 'Nominee',
                'entry_label' => 'Option',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Employee Recognition',
                'description' => 'Employee of the month/year voting',
                'icon' => 'fa-award',
                'participant_label' => 'Employee',
                'entry_label' => 'Nomination',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Art Competition',
                'description' => 'Art and creative competitions',
                'icon' => 'fa-palette',
                'participant_label' => 'Artist',
                'entry_label' => 'Artwork',
                'is_system' => true,
                'is_active' => true,
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
        Schema::dropIfExists('event_templates');
    }
};
