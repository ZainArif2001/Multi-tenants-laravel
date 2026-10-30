<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    public const STATUSES = ['new', 'open', 'replied', 'closed'];
    public const PRIORITIES = ['low', 'medium', 'high'];

    protected $fillable = [
        'name', 'email', 'subject', 'message', 'read_at',
        'status', 'priority', 'assigned_to', 'resolved_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isResolved(): bool
    {
        return $this->status === 'closed';
    }

    public static function statusCounts(): array
    {
        return static::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }
}
