<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = ['type', 'name'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('joined_at', 'pinned_at', 'archived_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * The other participant — private chats only.
     */
    public function otherUser(User $user): ?User
    {
        return $this->users->firstWhere('id', '!=', $user->id);
    }

    /**
     * Count of messages this user hasn't read yet.
     */
    public function unreadCountFor(User $user): int
    {
        return MessageReceipt::where('user_id', $user->id)
            ->whereNull('read_at')
            ->whereHas('message', fn ($q) => $q->where('conversation_id', $this->id)
                ->where('sender_id', '!=', $user->id))
            ->count();
    }
}
