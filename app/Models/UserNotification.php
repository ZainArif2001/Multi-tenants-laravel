<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'body', 'url', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Push a notification to a user.
     */
    public static function notify(int $userId, string $type, string $title, ?string $body = null, ?string $url = null): self
    {
        return static::create([
            'user_id' => $userId, 'type' => $type, 'title' => $title, 'body' => $body, 'url' => $url,
        ]);
    }
}
