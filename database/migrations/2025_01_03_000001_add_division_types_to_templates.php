<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add division_types JSON field to templates (if not exists)
        if (!Schema::hasColumn('event_templates', 'division_types')) {
            Schema::table('event_templates', function (Blueprint $table) {
                $table->json('division_types')->nullable()->after('entry_label');
            });
        }

        // Add type field to divisions (if not exists)
        if (!Schema::hasColumn('divisions', 'type')) {
            Schema::table('divisions', function (Blueprint $table) {
                $table->string('type', 50)->nullable()->after('code');
            });
        }

        // Update existing templates with default division types
        DB::table('event_templates')
            ->where('name', 'Food Competition')
            ->update([
                'division_types' => json_encode([
                    ['code' => 'P', 'name' => 'Professional', 'description' => 'Professional chefs and restaurants'],
                    ['code' => 'A', 'name' => 'Amateur', 'description' => 'Home cooks and hobbyists'],
                ])
            ]);

        DB::table('event_templates')
            ->where('name', 'Photo Contest')
            ->update([
                'division_types' => json_encode([
                    ['code' => 'N', 'name' => 'Nature', 'description' => 'Nature and landscape photography'],
                    ['code' => 'P', 'name' => 'Portrait', 'description' => 'Portrait and people photography'],
                    ['code' => 'S', 'name' => 'Street', 'description' => 'Street and urban photography'],
                ])
            ]);

        DB::table('event_templates')
            ->where('name', 'Talent Show')
            ->update([
                'division_types' => json_encode([
                    ['code' => 'V', 'name' => 'Vocal', 'description' => 'Singing and vocal performances'],
                    ['code' => 'I', 'name' => 'Instrumental', 'description' => 'Musical instrument performances'],
                    ['code' => 'D', 'name' => 'Dance', 'description' => 'Dance performances'],
                ])
            ]);

        // Update existing divisions to have type based on their code prefix
        DB::table('divisions')
            ->whereRaw("code LIKE 'P%'")
            ->update(['type' => 'Professional']);

        DB::table('divisions')
            ->whereRaw("code LIKE 'A%'")
            ->update(['type' => 'Amateur']);
    }

    public function down(): void
    {
        Schema::table('event_templates', function (Blueprint $table) {
            if (Schema::hasColumn('event_templates', 'division_types')) {
                $table->dropColumn('division_types');
            }
        });

        Schema::table('divisions', function (Blueprint $table) {
            if (Schema::hasColumn('divisions', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
