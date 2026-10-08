<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenantSeeder extends Seeder
{
    /**
     * Seed roles & permissions inside the tenant database.
     * Runs via Jobs\SeedDatabase (tenants:seed) after tenant migrations.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.manage',
            'posts.view', 'posts.create', 'posts.edit', 'posts.publish', 'posts.delete',
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'projects.view', 'projects.manage',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.update_status',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // admin: full access — use ->givePermissionTo(Permission::all()) if you want strict perms.
        // Spatie: users with a role having all permissions get everything.
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        Role::firstOrCreate(['name' => 'writer'])->syncPermissions([
            'posts.view', 'posts.create', 'posts.edit', 'posts.delete',
            'tasks.view', 'tasks.update_status',
        ]);

        Role::firstOrCreate(['name' => 'hr'])->syncPermissions([
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'tasks.view',
        ]);

        Role::firstOrCreate(['name' => 'member'])->syncPermissions([
            'tasks.view', 'tasks.update_status',
        ]);
    }
}
