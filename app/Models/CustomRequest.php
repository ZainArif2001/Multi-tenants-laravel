<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomRequest extends Model
{
    // Lives in the CENTRAL database — keep it there even inside tenant context
    protected $connection = 'mysql';

    protected $fillable = ['tenant_id', 'title', 'details', 'module', 'status', 'admin_note'];
}
