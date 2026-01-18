<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AI Knowledge Base - Domain-specific knowledge for AI responses
        Schema::create('ai_knowledge_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('general'); // general, voting, events, troubleshooting
            $table->text('content');
            $table->json('keywords')->nullable(); // For search matching
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher = more important
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });

        // AI Prompt Templates - Customizable system prompts
        Schema::create('ai_prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('context')->default('general'); // general, event_management, voting, results
            $table->text('system_prompt');
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // AI Conversations - Persist conversation sessions
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->unique(); // For anonymous users
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active'); // active, archived, deleted
            $table->json('metadata')->nullable(); // Extra context data
            $table->timestamps();

            $table->index('session_id');
            $table->index('user_id');
        });

        // AI Messages - Individual messages in conversations
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');
            $table->string('type')->default('text'); // text, wizard, error
            $table->json('metadata')->nullable(); // visualAids, wizardState, etc.
            $table->integer('tokens_used')->nullable();
            $table->timestamps();

            $table->index('ai_conversation_id');
        });

        // AI Tools - Available tools/capabilities
        Schema::create('ai_tools', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 'create_event', 'view_results'
            $table->string('name');
            $table->text('description');
            $table->string('category'); // wizard, query, action
            $table->json('parameters')->nullable(); // Expected parameters schema
            $table->string('handler_class')->nullable(); // PHP handler class
            $table->boolean('requires_event')->default(false);
            $table->boolean('requires_auth')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tools');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('ai_prompt_templates');
        Schema::dropIfExists('ai_knowledge_documents');
    }
};
