<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = ['post_id', 'name', 'email', 'body', 'approved'];

    protected $casts = ['approved' => 'boolean'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
