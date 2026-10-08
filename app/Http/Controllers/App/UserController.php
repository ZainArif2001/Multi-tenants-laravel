<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the tenant's users.
     */
    public function index(): View
    {
        $users = User::with('roles')->latest()->get();

        return view('app.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::pluck('name', 'name');

        return view('app.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in the tenant database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::exists('roles', 'name')],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('tenant.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $roles = Role::pluck('name', 'name');

        return view('app.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::exists('roles', 'name')],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        $user->syncRoles($validated['role']);

        return redirect()->route('tenant.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from the tenant database.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('tenant.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('tenant.users.index')->with('success', 'User deleted successfully.');
    }
}
