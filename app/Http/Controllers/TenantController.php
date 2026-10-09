<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::with('domains')->latest()->get();
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validtionData = $request->validate([
            'name' => 'required|string|max:255',
            'domain_name' => 'required|string|max:255|unique:domains,domain',
            'email' => 'required|email|max:255|unique:tenants,email',
            'password' => 'required|string|min:8|confirmed',
            'modules' => 'nullable|array',
            'modules.*' => 'in:'.implode(',', array_keys(Tenant::MODULES)),
        ]);
        // dd($validtionData);

        $tenant = Tenant::create([
            ...$validtionData,
            // modules is a virtual attribute → stored in the JSON `data` column
            'modules' => $validtionData['modules'] ?? [],
        ]);
        $tenant->domains()->create([
            'domain' => $validtionData['domain_name'].'.'.config('app.domain'),
        ]);

        return redirect()->route('tenants.index')->with('success', 'Tenant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validtionData = $request->validate([
            'name' => 'required|string|max:255',
            'domain_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants,email,'.$tenant->id,
            'password' => 'nullable|string|min:8|confirmed',
            'modules' => 'nullable|array',
            'modules.*' => 'in:'.implode(',', array_keys(Tenant::MODULES)),
        ]);

        if (empty($validtionData['password'])) {
            unset($validtionData['password']);
        }

        $tenant->update([
            ...$validtionData,
            'modules' => $validtionData['modules'] ?? [],
        ]);

        $domain = $validtionData['domain_name'].'.'.config('app.domain');
        $tenant->domains()->updateOrCreate(['tenant_id' => $tenant->id], ['domain' => $domain]);

        return redirect()->route('tenants.index')->with('success', 'Tenant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return redirect()->route('tenants.index')->with('success', 'Tenant deleted successfully.');
    }
}
