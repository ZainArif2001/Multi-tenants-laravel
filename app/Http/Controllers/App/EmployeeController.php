<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobRole;
use App\Models\Manager;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::with('department', 'jobRole', 'manager')
            ->withCount('projects')
            ->latest()->get();

        return view('app.employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('app.employees.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = Employee::create($this->validated($request));
        $this->syncProjects($employee, $request->input('projects', []));

        return redirect()->route('tenant.employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee): View
    {
        $employee->load('department', 'jobRole', 'manager', 'projects');

        return view('app.employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $employee->load('projects');

        return view('app.employees.edit', ['employee' => $employee, ...$this->formData()]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $employee->update($this->validated($request));
        $this->syncProjects($employee, $request->input('projects', []));

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
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'job_title' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'job_role_id' => 'nullable|exists:job_roles,id',
            'manager_id' => 'nullable|exists:managers,id',
            'projects' => 'nullable|array',
            'projects.*.id' => 'exists:projects,id',
            'projects.*.hours' => 'nullable|integer|min:0|max:9999',
        ]);
    }

    /**
     * Works_On pivot — each selected project gets its assigned_hours.
     * Input shape: projects[12][id]=12&projects[12][hours]=20
     */
    protected function syncProjects(Employee $employee, array $projects): void
    {
        $sync = collect($projects)
            ->filter(fn ($p) => filled($p['id'] ?? null) && filled($p['selected'] ?? null))
            ->mapWithKeys(fn ($p) => [(int) $p['id'] => ['assigned_hours' => ($p['hours'] ?? null) ?: null]]);

        $employee->projects()->sync($sync);
    }

    protected function formData(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(),
            'jobRoles' => JobRole::orderBy('name')->get(),
            'managers' => Manager::orderBy('name')->get(),
            'projects' => tenant()->hasModule('projects') ? Project::orderBy('name')->get() : collect(),
        ];
    }
}
