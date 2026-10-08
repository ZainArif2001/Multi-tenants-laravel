<?php

declare(strict_types=1);

use App\Http\Controllers\App\EmployeeController;
use App\Http\Controllers\App\PostController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\ProjectController;
use App\Http\Controllers\App\TaskController;
use App\Http\Controllers\App\UserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Routes here only run on tenant domains — tenancy is initialized from
| the domain, then all queries hit the tenant's own database.
|
| All route names are prefixed with "tenant." (e.g. tenant.login,
| tenant.dashboard) so they never collide with central route names.
|
| Modules are gated by Spatie permissions seeded per tenant
| (posts.*, employees.*, projects.*, tasks.*, users.manage).
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->name('tenant.')->group(function () {
    Route::get('/', function () {
        return view('app.welcome');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return view('app.dashboard', [
                'usersCount' => \App\Models\User::count(),
                'postsCount' => \App\Models\Post::count(),
                'employeesCount' => \App\Models\Employee::count(),
                'projectsCount' => \App\Models\Project::count(),
                'tasksCount' => \App\Models\Task::count(),
            ]);
        })->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Users — admin only
        Route::resource('users', UserController::class)
            ->except('show')
            ->middleware('role:admin');

        // Posts — writers create/edit own, admins can publish & manage all
        Route::get('posts', [PostController::class, 'index'])->name('posts.index')->middleware('permission:posts.view');
        Route::get('posts/create', [PostController::class, 'create'])->name('posts.create')->middleware('permission:posts.create');
        Route::post('posts', [PostController::class, 'store'])->name('posts.store')->middleware('permission:posts.create');
        Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit')->middleware('permission:posts.edit');
        Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update')->middleware('permission:posts.edit');
        Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy')->middleware('permission:posts.delete');

        // Employees — hr + admin
        Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index')->middleware('permission:employees.view');
        Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create')->middleware('permission:employees.create');
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:employees.create');
        Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:employees.edit');
        Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update')->middleware('permission:employees.edit');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('permission:employees.delete');

        // Projects — admin manages, members view
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index')->middleware('permission:projects.view');
        Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create')->middleware('permission:projects.manage');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store')->middleware('permission:projects.manage');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show')->middleware('permission:projects.view');
        Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit')->middleware('permission:projects.manage');
        Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update')->middleware('permission:projects.manage');
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy')->middleware('permission:projects.manage');

        // Tasks — view all, manage restricted, status updates for assignees
        Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index')->middleware('permission:tasks.view');
        Route::get('tasks/create', [TaskController::class, 'create'])->name('tasks.create')->middleware('permission:tasks.create');
        Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store')->middleware('permission:tasks.create');
        Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit')->middleware('permission:tasks.edit');
        Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update')->middleware('permission:tasks.edit');
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status')->middleware('permission:tasks.update_status');
        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy')->middleware('permission:tasks.delete');
    });

    require __DIR__.'/tenant-auth.php';
});
