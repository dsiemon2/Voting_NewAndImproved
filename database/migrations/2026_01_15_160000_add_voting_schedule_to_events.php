<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('events', 'voting_starts_at')) {
            Schema::table('events', function (Blueprint $table) {
                $table->timestamp('voting_starts_at')->nullable()->after('event_date');
                $table->timestamp('voting_ends_at')->nullable()->after('voting_starts_at');
                $table->boolean('auto_publish_results')->default(false)->after('voting_ends_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['voting_starts_at', 'voting_ends_at', 'auto_publish_results']);
        });
    }
};
