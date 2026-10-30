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
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::with('department', 'jobRole', 'manager')
            ->withCount('projects')
            ->latest()->paginate(15);

        return view('app.employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('app.employees.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = Employee::create($this->validated($request));
        \App\Models\Webhook::dispatch('employee.created', $employee->only('first_name', 'last_name', 'email', 'job_title'));
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

    /**
     * Bulk import — CSV or XLSX. Expected headers (first row):
     * first_name, last_name, email, phone, job_title, salary, hire_date,
     * department, role, manager. Departments/roles/managers are
     * auto-created by name when they don't exist.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        $rows = Excel::toArray(null, $request->file('file'))[0] ?? [];
        $headers = array_map(fn ($h) => Str::of($h)->lower()->snake()->toString(), $rows[0] ?? []);

        // Fuzzy header aliases — accept common variants of each column
        $aliases = [
            'first_name' => ['first_name', 'firstname', 'first', 'fname', 'name', 'given_name'],
            'last_name' => ['last_name', 'lastname', 'last', 'lname', 'surname', 'family_name'],
            'email' => ['email', 'e_mail', 'email_address', 'mail'],
            'phone' => ['phone', 'phone_number', 'mobile', 'contact', 'tel', 'telephone'],
            'job_title' => ['job_title', 'jobtitle', 'title', 'position', 'designation'],
            'salary' => ['salary', 'pay', 'wage', 'compensation', 'ctc'],
            'hire_date' => ['hire_date', 'hiredate', 'joining_date', 'date_joined', 'joined', 'start_date'],
            'department' => ['department', 'dept', 'team', 'division'],
            'role' => ['role', 'job_role', 'jobrole', 'level'],
            'manager' => ['manager', 'manager_name', 'reports_to', 'supervisor', 'boss'],
        ];
        $headers = array_map(function ($h) use ($aliases) {
            foreach ($aliases as $canonical => $names) {
                if (in_array($h, $names)) return $canonical;
            }

            return $h;
        }, $headers);

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach (array_slice($rows, 1) as $i => $row) {
            $data = array_combine($headers, array_pad($row, count($headers), null));
            $rowNo = $i + 2;

            // Skip fully empty rows
            if (blank(implode('', array_map(fn ($v) => (string) $v, $row)))) {
                continue;
            }

            if (blank($data['first_name'] ?? null)) {
                $skipped++;
                $errors[] = "Row {$rowNo}: missing first_name";
                continue;
            }

            if (filled($data['email'] ?? null) && Employee::where('email', $data['email'])->exists()) {
                $skipped++;
                $errors[] = "Row {$rowNo}: {$data['email']} already exists";
                continue;
            }

            Employee::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => isset($data['phone']) ? (string) $data['phone'] : null,
                'job_title' => $data['job_title'] ?? null,
                'salary' => is_numeric($data['salary'] ?? null) ? $data['salary'] : null,
                'hire_date' => $this->parseDate($data['hire_date'] ?? null),
                'department_id' => $this->resolveByName(Department::class, $data['department'] ?? null),
                'job_role_id' => $this->resolveByName(JobRole::class, $data['role'] ?? null),
                'manager_id' => $this->resolveByName(Manager::class, $data['manager'] ?? null),
            ]);
            $created++;
        }

        $message = "{$created} employees imported".($skipped ? ", {$skipped} skipped." : '.');

        return redirect()->route('tenant.employees.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /**
     * Downloadable CSV template matching the import headers.
     */
    public function template()
    {
        $csv = implode("\n", [
            'first_name,last_name,email,phone,job_title,salary,hire_date,department,role,manager',
            'Ali,Khan,ali@example.com,03001234567,Senior Developer,120000,2025-01-15,Engineering,Developer,Zain Arif',
            'Sara,Ahmed,sara@example.com,03111234567,HR Officer,80000,2025-03-01,HR,HR Executive,Fatima Noor',
        ]);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employees-template.csv"',
        ]);
    }

    protected function resolveByName(string $model, ?string $name): ?int
    {
        $name = trim((string) $name);

        return filled($name) ? $model::firstOrCreate(['name' => $name])->id : null;
    }

    protected function parseDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }
        // Excel serial date (e.g. 45672)
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        $ts = strtotime((string) $value);

        return $ts ? date('Y-m-d', $ts) : null;
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
