<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // openai, anthropic, gemini, deepseek, etc.
            $table->string('name'); // OpenAI, Anthropic Claude, Google Gemini, DeepSeek
            $table->string('description')->nullable();
            $table->text('api_key')->nullable(); // encrypted
            $table->string('api_base_url')->nullable(); // custom base URL for self-hosted
            $table->json('available_models')->nullable(); // list of available models
            $table->string('default_model')->nullable(); // default model for this provider
            $table->boolean('is_active')->default(false);
            $table->boolean('is_configured')->default(false); // has valid API key
            $table->boolean('is_selected')->default(false); // currently selected provider
            $table->integer('display_order')->default(0);
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->integer('max_tokens')->default(4096);
            $table->json('settings')->nullable(); // provider-specific settings
            $table->timestamps();
        });

        // Add provider reference to ai_configs
        Schema::table('ai_configs', function (Blueprint $table) {
            $table->foreignId('active_provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ai_configs', function (Blueprint $table) {
            $table->dropForeign(['active_provider_id']);
            $table->dropColumn('active_provider_id');
        });

        Schema::dropIfExists('ai_providers');
    }
};
