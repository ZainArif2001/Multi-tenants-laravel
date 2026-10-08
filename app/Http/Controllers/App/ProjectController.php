<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::withCount('tasks')->latest()->get();

        return view('app.projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('app.projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Project::create($this->validated($request));

        return redirect()->route('tenant.projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load('tasks.assignee');

        return view('app.projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        return view('app.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validated($request));

        return redirect()->route('tenant.projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('tenant.projects.index')->with('success', 'Project deleted successfully.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,on_hold,completed',
        ]);
    }
}
