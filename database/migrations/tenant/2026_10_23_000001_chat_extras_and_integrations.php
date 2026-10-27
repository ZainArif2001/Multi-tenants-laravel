<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Message reactions (emoji)
        if (! Schema::hasTable('message_reactions')) {
            Schema::create('message_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('emoji', 10);
                $table->timestamps();
                $table->unique(['message_id', 'user_id', 'emoji']);
            });
        }

        // Reply-to support
        if (! Schema::hasColumn('messages', 'reply_to_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->foreignId('reply_to_id')->nullable()->after('sender_id')
                    ->constrained('messages')->nullOnDelete();
                $table->boolean('is_ai')->default(false)->after('reply_to_id');
            });
        }

        // Per-user conversation preferences
        if (! Schema::hasColumn('conversation_user', 'pinned_at')) {
            Schema::table('conversation_user', function (Blueprint $table) {
                $table->timestamp('pinned_at')->nullable();
                $table->timestamp('archived_at')->nullable();
            });
        }

        // AI knowledge base — uploaded docs feeding the visitor chatbot
        if (! Schema::hasTable('knowledge_docs')) {
            Schema::create('knowledge_docs', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('file_path')->nullable();
                $table->longText('content');
                $table->timestamps();
            });
        }

        // Outbound webhooks
        if (! Schema::hasTable('webhooks')) {
            Schema::create('webhooks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('url');
                $table->string('secret')->nullable();
                $table->json('events');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_reactions');
        Schema::dropIfExists('knowledge_docs');
        Schema::dropIfExists('webhooks');
        Schema::table('messages', fn (Blueprint $t) => $t->dropColumn(['reply_to_id', 'is_ai']));
        Schema::table('conversation_user', fn (Blueprint $t) => $t->dropColumn(['pinned_at', 'archived_at']));
    }
};
