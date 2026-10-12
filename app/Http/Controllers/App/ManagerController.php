<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManagerController extends Controller
{
    public function index(): View
    {
        $managers = Manager::withCount('employees')->latest()->get();

        return view('app.managers.index', compact('managers'));
    }

    public function store(Request $request): RedirectResponse
    {
        Manager::create($request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]));

        return back()->with('success', 'Manager created.');
    }

    public function update(Request $request, Manager $manager): RedirectResponse
    {
        $manager->update($request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]));

        return back()->with('success', 'Manager updated.');
    }

    public function destroy(Manager $manager): RedirectResponse
    {
        $manager->delete();

        return back()->with('success', 'Manager deleted.');
    }
}
