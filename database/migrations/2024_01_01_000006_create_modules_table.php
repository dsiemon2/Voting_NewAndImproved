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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('route_prefix', 50)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_core')->default(false);
            $table->timestamps();
        });

        // Seed system modules
        DB::table('modules')->insert([
            ['code' => 'divisions', 'name' => 'Divisions', 'description' => 'Organize entries into divisions or tiers', 'icon' => 'fa-layer-group', 'route_prefix' => 'divisions', 'display_order' => 10, 'is_core' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'participants', 'name' => 'Participants', 'description' => 'Manage participants/contestants', 'icon' => 'fa-users', 'route_prefix' => 'participants', 'display_order' => 20, 'is_core' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'categories', 'name' => 'Categories', 'description' => 'Event categories for voting', 'icon' => 'fa-tags', 'route_prefix' => 'categories', 'display_order' => 30, 'is_core' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'entries', 'name' => 'Entries', 'description' => 'Manage items being voted on', 'icon' => 'fa-clipboard-list', 'route_prefix' => 'entries', 'display_order' => 40, 'is_core' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'import', 'name' => 'Import', 'description' => 'Bulk import from spreadsheets', 'icon' => 'fa-file-import', 'route_prefix' => 'import', 'display_order' => 50, 'is_core' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'voting', 'name' => 'Voting', 'description' => 'Cast votes', 'icon' => 'fa-vote-yea', 'route_prefix' => 'voting', 'display_order' => 60, 'is_core' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'results', 'name' => 'Results', 'description' => 'View voting results', 'icon' => 'fa-chart-bar', 'route_prefix' => 'results', 'display_order' => 70, 'is_core' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'reports', 'name' => 'Reports', 'description' => 'Generate detailed reports', 'icon' => 'fa-file-alt', 'route_prefix' => 'reports', 'display_order' => 80, 'is_core' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'pdf', 'name' => 'PDF Export', 'description' => 'Print ballots and results', 'icon' => 'fa-file-pdf', 'route_prefix' => 'pdf', 'display_order' => 90, 'is_core' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'judging', 'name' => 'Judging Panel', 'description' => 'Professional judges with weighted votes', 'icon' => 'fa-gavel', 'route_prefix' => 'judging', 'display_order' => 100, 'is_core' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
