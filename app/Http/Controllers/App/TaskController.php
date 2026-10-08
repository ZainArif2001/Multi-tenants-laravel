<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = Task::with('project', 'assignee')->latest()->get();

        return view('app.tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        $projects = Project::pluck('name', 'id');
        $users = User::pluck('name', 'id');

        return view('app.tasks.create', compact('projects', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        Task::create($this->validated($request));

        return redirect()->route('tenant.tasks.index')->with('success', 'Task created successfully.');
    }

    public function edit(Task $task): View
    {
        $projects = Project::pluck('name', 'id');
        $users = User::pluck('name', 'id');

        return view('app.tasks.edit', compact('task', 'projects', 'users'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $task->update($this->validated($request));

        return redirect()->route('tenant.tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Lightweight status update — assigned users (members) update only their own tasks.
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $request->validate(['status' => 'required|in:todo,in_progress,done']);

        $canManage = $request->user()->can('tasks.edit');
        abort_unless($canManage || $task->assigned_to === $request->user()->id, 403);

        $task->update(['status' => $request->status]);

        return back()->with('success', 'Task status updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tenant.tasks.index')->with('success', 'Task deleted successfully.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,done',
            'due_date' => 'nullable|date',
        ]);
    }
}
