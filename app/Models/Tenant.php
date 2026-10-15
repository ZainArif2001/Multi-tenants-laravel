<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Modules a tenant can be given access to. "tasks" ride along
     * with "projects" — a tenant without projects has no tasks.
     */
    public const MODULES = [
        'posts' => 'Posts',
        'employees' => 'Employees',
        'projects' => 'Projects & Tasks',
        'chat' => 'AI Chatbot',
        'messaging' => 'Team Chat',
    ];

    /**
     * Subscription-style plans — picking one pre-selects its modules
     * on the create-tenant form.
     */
    public const PLANS = [
        'starter' => ['label' => 'Starter', 'modules' => ['posts']],
        'business' => ['label' => 'Business', 'modules' => ['posts', 'employees', 'messaging']],
        'agency' => ['label' => 'Agency', 'modules' => ['posts', 'employees', 'projects', 'chat', 'messaging']],
        'custom' => ['label' => 'Custom', 'modules' => []],
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'password',
        ];
    }

    public function setpasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * modules lives in the JSON `data` column. null = legacy tenant
     * created before modules existed → treat as all enabled.
     */
    public function hasModule(string $module): bool
    {
        $modules = $this->modules;

        if ($modules === null) {
            return true;
        }

        $module = $module === 'tasks' ? 'projects' : $module;

        return in_array($module, $modules);
    }
}