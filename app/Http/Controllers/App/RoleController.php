<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('app.roles.index', [
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all()->groupBy(fn ($p) => explode('.', $p->name)[0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name|regex:/^[a-z0-9_]+$/',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        Role::create(['name' => $data['name'], 'guard_name' => 'web'])
            ->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', "Role '{$data['name']}' created.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);
        abort_if($role->name === 'admin', 422); // admin keeps full access

        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', "Role '{$role->name}' updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if(in_array($role->name, ['admin', 'member']), 422);
        abort_if($role->users()->exists(), 422); // reassign users first

        $role->delete();

        return back()->with('success', "Role '{$role->name}' deleted.");
    }
}
