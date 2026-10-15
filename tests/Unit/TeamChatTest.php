<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\Tenant;
use App\Models\User;

uses(Tests\TestCase::class); // Laravel app, no RefreshDatabase (real MySQL)

/**
 * Team Chat — send → delivered → read lifecycle, per-recipient receipts,
 * and per-user module gating. Runs inside the real democo tenant context
 * (MySQL) and cleans up everything it creates.
 */

beforeEach(function () {
    $this->tenant = Tenant::find('ea5b844b-c924-4ce2-9248-ee6fe19ce762');
    $this->tenant->run(function () {
        $this->a = User::create(['name' => 'Test Alice', 'email' => 'test-a@x.dev', 'password' => bcrypt('x'), 'allowed_modules' => ['messaging']]);
        $this->b = User::create(['name' => 'Test Bob', 'email' => 'test-b@x.dev', 'password' => bcrypt('x'), 'allowed_modules' => ['messaging']]);
    });
});

afterEach(function () {
    $this->tenant->run(function () {
        User::whereIn('email', ['test-a@x.dev', 'test-b@x.dev'])->delete();
    });
});

test('private conversation holds two members', function () {
    $this->tenant->run(function () {
        $c = Conversation::create(['type' => 'private']);
        $c->users()->attach([$this->a->id, $this->b->id], ['joined_at' => now()]);

        expect($c->users)->toHaveCount(2)
            ->and($c->otherUser($this->a)->id)->toBe($this->b->id);

        $c->delete();
    });
});

test('message receipt lifecycle goes sent → delivered → read', function () {
    $this->tenant->run(function () {
        $c = Conversation::create(['type' => 'private']);
        $c->users()->attach([$this->a->id, $this->b->id]);

        $m = Message::create(['conversation_id' => $c->id, 'sender_id' => $this->a->id, 'body' => 'hi']);
        MessageReceipt::create(['message_id' => $m->id, 'user_id' => $this->b->id]);

        expect($m->statusFor($this->a))->toBe('sent')
            ->and($m->statusFor($this->b))->toBe('received')
            ->and($c->unreadCountFor($this->b))->toBe(1);

        $m->receipts()->update(['delivered_at' => now()]);
        expect($m->fresh()->statusFor($this->a))->toBe('delivered');

        $m->receipts()->update(['read_at' => now()]);
        expect($m->fresh()->statusFor($this->a))->toBe('read')
            ->and($c->unreadCountFor($this->b))->toBe(0);

        $c->delete();
    });
});

test('allowed_modules gates module access per user', function () {
    $this->tenant->run(function () {
        expect($this->a->hasAccessTo('messaging'))->toBeTrue()
            ->and($this->a->hasAccessTo('posts'))->toBeFalse();
    });
});

test('tenant module flag controls feature availability', function () {
    expect($this->tenant->hasModule('messaging'))->toBeTrue();
});
