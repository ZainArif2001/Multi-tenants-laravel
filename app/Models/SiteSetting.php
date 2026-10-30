<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'tagline', 'description', 'contact_email',
        'facebook_url', 'twitter_url', 'linkedin_url',
        'logo_path', 'hero_title', 'hero_subtitle',
        'show_hero', 'show_posts', 'show_contact', 'footer_text',
    ];

    protected $casts = [
        'show_hero' => 'boolean',
        'show_posts' => 'boolean',
        'show_contact' => 'boolean',
    ];

    /**
     * The single settings row for this tenant (auto-created).
     */
    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? url('/media/'.$this->logo_path) : null;
    }
}
