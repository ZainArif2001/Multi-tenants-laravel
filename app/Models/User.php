<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'avatar_path',
        'email',
        'password',
        'allowed_modules',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'allowed_modules' => 'array',
        ];
    }

    /**
     * Per-user module access — the tenant admin picks which projects
     * a user can open. Admins get everything; legacy users (null)
     * fall back to access derived from their role.
     */
    public function hasAccessTo(string $module): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        $module = $module === 'tasks' ? 'projects' : $module;
        $modules = $this->allowed_modules;

        if ($modules === null) {
            // Legacy users — messaging stays on for everyone; the rest
            // is derived from their role.
            return match (true) {
                $module === 'messaging' => true,
                $this->hasRole('writer') => in_array($module, ['posts', 'projects']),
                $this->hasRole('hr') => in_array($module, ['employees', 'projects', 'chat']),
                $this->hasRole('member') => in_array($module, ['projects', 'chat']),
                default => false,
            };
        }

        return in_array($module, $modules);
    }

    public function conversations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Conversation::class)->withPivot('joined_at');
    }

    /**
     * Uploaded avatar served via the tenant-aware media route.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? route('tenant.media', ['path' => $this->avatar_path]) : null;
    }

    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? 'U', 0, 1));
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
