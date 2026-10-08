<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::latest()->get();

        return view('app.employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('app.employees.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Employee::create($this->validated($request));

        return redirect()->route('tenant.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee): View
    {
        return view('app.employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $employee->update($this->validated($request));

        return redirect()->route('tenant.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('tenant.employees.index')->with('success', 'Employee deleted successfully.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'joined_at' => 'nullable|date',
        ]);
    }
}
