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
        Schema::create('event_template_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_template_id')->constrained('event_templates')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_enabled_by_default')->default(true);
            $table->integer('display_order')->default(0);
            $table->string('custom_label', 100)->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['event_template_id', 'module_id']);
        });

        // Get template and module IDs
        $foodCompId = DB::table('event_templates')->where('name', 'Food Competition')->value('id');
        $photoId = DB::table('event_templates')->where('name', 'Photo Contest')->value('id');
        $generalId = DB::table('event_templates')->where('name', 'General Vote')->value('id');

        $modules = DB::table('modules')->pluck('id', 'code');

        // Food Competition modules
        $foodModules = ['divisions', 'participants', 'categories', 'entries', 'import', 'voting', 'results', 'reports', 'pdf'];
        foreach ($foodModules as $order => $code) {
            DB::table('event_template_modules')->insert([
                'event_template_id' => $foodCompId,
                'module_id' => $modules[$code],
                'is_required' => in_array($code, ['voting', 'results']),
                'is_enabled_by_default' => true,
                'display_order' => ($order + 1) * 10,
                'custom_label' => $code === 'participants' ? 'Chefs' : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Photo Contest modules
        $photoModules = ['categories', 'participants', 'entries', 'voting', 'results', 'reports', 'judging'];
        foreach ($photoModules as $order => $code) {
            DB::table('event_template_modules')->insert([
                'event_template_id' => $photoId,
                'module_id' => $modules[$code],
                'is_required' => in_array($code, ['voting', 'results']),
                'is_enabled_by_default' => true,
                'display_order' => ($order + 1) * 10,
                'custom_label' => $code === 'participants' ? 'Photographers' : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // General Vote modules (minimal)
        $generalModules = ['entries', 'voting', 'results'];
        foreach ($generalModules as $order => $code) {
            DB::table('event_template_modules')->insert([
                'event_template_id' => $generalId,
                'module_id' => $modules[$code],
                'is_required' => true,
                'is_enabled_by_default' => true,
                'display_order' => ($order + 1) * 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_template_modules');
    }
};
