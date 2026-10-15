<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'tagline', 'description', 'contact_email',
        'facebook_url', 'twitter_url', 'linkedin_url',
    ];

    /**
     * The single settings row for this tenant (auto-created).
     */
    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }
}
