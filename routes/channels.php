<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Team Chat — per-user channel so every device gets pushes
// (delivered/read works even when a different chat is open)
Broadcast::channel('user.{userId}', fn (User $user, int $userId) => $user->id === $userId);

// Conversation channel — members only
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    return Conversation::whereKey($conversationId)
        ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
        ->exists();
});
