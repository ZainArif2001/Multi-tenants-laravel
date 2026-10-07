<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Routes here only exist on tenant domains. Auth (login/register) is
| intentionally NOT registered here, so those pages 404 on tenants.
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        dd(tenant()->toArray());
        // dd(tenant('id'));
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
    Route::get('login', function () {
       dd('login page');
    });
});
