<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('private');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('conversation_user')) {
            Schema::create('conversation_user', function (Blueprint $table) {
                $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('joined_at')->nullable();
                $table->primary(['conversation_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sender_id')->constrained('users');
                $table->text('body');
                $table->timestamp('edited_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
                $table->index(['conversation_id', 'id']);
            });
        }

        // Per-recipient delivery/read status — survives group chats too
        if (! Schema::hasTable('message_receipts')) {
            Schema::create('message_receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->unique(['message_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_receipts');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_user');
        Schema::dropIfExists('conversations');
    }
};
