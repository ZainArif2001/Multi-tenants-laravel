<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // In-app notification center
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Employee leave requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('annual');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        // Visitor live-chat handoff flag
        if (! Schema::hasColumn('chat_messages', 'handoff')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->boolean('handoff')->default(false);
            });
        }

        // TOTP 2FA
        if (! Schema::hasColumn('users', 'google2fa_secret')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('google2fa_secret')->nullable();
                $table->text('two_factor_recovery_codes')->nullable();
                $table->timestamp('onboarded_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('leave_requests');
        Schema::table('chat_messages', fn (Blueprint $t) => $t->dropColumn('handoff'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['google2fa_secret', 'two_factor_recovery_codes', 'onboarded_at']));
    }
};
