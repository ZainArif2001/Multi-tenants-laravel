<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'department', 'position', 'joined_at'];

    protected $casts = ['joined_at' => 'date'];
}
