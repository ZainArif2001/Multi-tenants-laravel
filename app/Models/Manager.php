<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manager extends Model
{
    protected $fillable = ['name', 'email', 'phone'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
