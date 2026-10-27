<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeDoc extends Model
{
    protected $fillable = ['title', 'file_path', 'content'];
}
