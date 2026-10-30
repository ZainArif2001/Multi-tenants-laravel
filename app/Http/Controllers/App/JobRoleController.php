<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\JobRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobRoleController extends Controller
{
    public function index(): View
    {
        $jobRoles = JobRole::withCount('employees')->latest()->paginate(15);

        return view('app.job-roles.index', compact('jobRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        JobRole::create($request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]));

        return back()->with('success', 'Role created.');
    }

    public function update(Request $request, JobRole $jobRole): RedirectResponse
    {
        $jobRole->update($request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]));

        return back()->with('success', 'Role updated.');
    }

    public function destroy(JobRole $jobRole): RedirectResponse
    {
        $jobRole->delete();

        return back()->with('success', 'Role deleted.');
    }
}
