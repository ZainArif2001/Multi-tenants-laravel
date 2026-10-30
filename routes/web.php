<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('tenants', TenantController::class);
    Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
    Route::get('tenants/check-domain', [TenantController::class, 'checkDomain'])->name('tenants.check-domain');
    Route::post('tenants/{tenant}/approve-domain', [TenantController::class, 'approveDomain'])->name('tenants.approve-domain');
    Route::post('tenants/{tenant}/impersonate', [TenantController::class, 'impersonate'])->name('tenants.impersonate');

    // Customization requests from tenants — super admin reviews
    Route::get('requests', function () {
        return view('requests.index', [
            'requests' => \App\Models\CustomRequest::latest()->get(),
            'tenants' => \App\Models\Tenant::pluck('name', 'id'),
        ]);
    })->name('requests.index');
    Route::patch('requests/{customRequest}', function (\Illuminate\Http\Request $r, \App\Models\CustomRequest $customRequest) {
        $customRequest->update($r->validate([
            'status' => 'required|in:pending,in_progress,done,rejected',
            'admin_note' => 'nullable|string|max:1000',
        ]));

        return back()->with('success', 'Request updated.');
    })->name('requests.update');
});

require __DIR__.'/auth.php';
