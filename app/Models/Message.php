<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = ['conversation_id', 'sender_id', 'body', 'edited_at'];

    protected $casts = ['edited_at' => 'datetime'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(MessageReceipt::class);
    }

    /**
     * Aggregate receipt status for the sender — sent → delivered → read.
     * In groups: delivered when ALL recipients got it, read when ALL read it.
     */
    public function statusFor(User $viewer): string
    {
        if ($viewer->id === $this->sender_id) {
            $receipts = $this->receipts;
            if ($receipts->isEmpty()) {
                return 'sent';
            }
            if ($receipts->every(fn ($r) => $r->read_at)) {
                return 'read';
            }
            if ($receipts->every(fn ($r) => $r->delivered_at)) {
                return 'delivered';
            }

            return 'sent';
        }

        return 'received';
    }
}
