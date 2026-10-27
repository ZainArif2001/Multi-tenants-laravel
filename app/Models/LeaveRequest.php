<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = ['user_id', 'type', 'start_date', 'end_date', 'reason', 'status', 'admin_note'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public const TYPES = ['annual' => 'Annual', 'sick' => 'Sick', 'casual' => 'Casual', 'unpaid' => 'Unpaid'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
