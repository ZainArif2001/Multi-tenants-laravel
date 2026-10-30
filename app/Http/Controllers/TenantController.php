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
            'domain_name' => [
                'required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/',
                function ($attr, $value, $fail) {
                    $full = $value . '.' . config('app.domain');
                    if (\Stancl\Tenancy\Database\Models\Domain::where('domain', $full)->exists()
                        || \Stancl\Tenancy\Database\Models\Domain::where('domain', $value)->exists()) {
                        $fail("Domain \"{$full}\" is already taken — pick another.");
                    }
                },
            ],
            'email' => 'required|email|max:255|unique:tenants,email',
            'password' => 'required|string|min:8|confirmed',
            'plan' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(Tenant::PLANS))],
            'modules' => 'nullable|array',
            'modules.*' => 'in:'.implode(',', array_keys(Tenant::MODULES)),
        ]);
        // dd($validtionData);

        $tenant = Tenant::create([
            ...$validtionData,
            // modules + plan are virtual attributes → stored in the JSON `data` column
            'plan' => $validtionData['plan'] ?? 'custom',
            'modules' => $validtionData['modules'] ?? [],
        ]);
        $tenant->domains()->create([
            'domain' => $validtionData['domain_name'].'.'.config('app.domain'),
        ]);

        return redirect()->route('tenants.index')->with('success', 'Tenant created successfully.');
    }

    /**
     * Live domain availability check for the create form.
     */
    public function checkDomain(\Illuminate\Http\Request $request)
    {
        $name = strtolower(trim($request->query('d', '')));
        if ($name === '') {
            return response()->json(['available' => null]);
        }

        $full = $name . '.' . config('app.domain');
        $taken = \Stancl\Tenancy\Database\Models\Domain::where('domain', $full)
            ->orWhere('domain', $name)->exists();

        return response()->json(['available' => ! $taken, 'domain' => $full]);
    }

    /**
     * Suspend / resume a tenant — suspended tenants get a 503 on their domain.
     */
    public function suspend(Tenant $tenant)
    {
        $tenant->suspended = ! $tenant->suspended;
        $tenant->save();

        return back()->with('success', $tenant->suspended ? 'Tenant suspended.' : 'Tenant resumed.');
    }

    /**
     * Approve a tenant's pending custom domain → activates it.
     */
    public function approveDomain(Tenant $tenant)
    {
        abort_unless($tenant->pending_domain, 404);
        $tenant->domains()->firstOrCreate(
            ['domain' => $tenant->pending_domain],
            ['verified_at' => now()]
        );
        $tenant->pending_domain = null;
        $tenant->save();

        return back()->with('success', 'Domain activated.');
    }

    /**
     * Impersonate a tenant admin — generates a one-time token, redirects
     * into the tenant domain where it's consumed for login.
     */
    public function impersonate(Tenant $tenant)
    {
        $token = str()->random(40);
        $tenant->impersonation_token = $token;
        $tenant->save();

        $domain = $tenant->domains->first()->domain;

        return redirect("http://{$domain}/impersonate/{$token}");
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
