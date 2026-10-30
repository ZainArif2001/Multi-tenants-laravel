<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::withCount('employees')->latest()->paginate(15);

        return view('app.departments.index', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        Department::create($request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]));

        return back()->with('success', 'Department created.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]));

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return back()->with('success', 'Department deleted.');
    }
}
