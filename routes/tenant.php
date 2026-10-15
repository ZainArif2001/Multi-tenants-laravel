<?php

declare(strict_types=1);

use App\Http\Controllers\App\CategoryController;
use App\Http\Controllers\App\ChatController;
use App\Http\Controllers\App\CommentController;
use App\Http\Controllers\App\DepartmentController;
use App\Http\Controllers\App\EmployeeController;
use App\Http\Controllers\App\JobRoleController;
use App\Http\Controllers\App\ManagerController;
use App\Http\Controllers\App\SiteSettingsController;
use App\Http\Controllers\App\TeamChatController;
use App\Http\Controllers\App\PostController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\SiteController;
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
    // Public website — no auth; pages are gated by the tenant's enabled modules
    Route::get('/', [SiteController::class, 'home'])->name('home');

    Route::middleware('module:posts')->group(function () {
        Route::get('/blog', [SiteController::class, 'blog'])->name('blog');
        Route::get('/blog/{post}', [SiteController::class, 'post'])->name('blog.show');
        Route::post('/blog/{post}/comment', [CommentController::class, 'store'])->name('blog.comment');
        Route::get('/categories', [SiteController::class, 'categories'])->name('categories');
        Route::get('/category/{category}', [SiteController::class, 'category'])->name('category');
        Route::get('/author/{user}', [SiteController::class, 'author'])->name('author');
    });

    Route::get('/team', [SiteController::class, 'team'])->name('team')->middleware('module:employees');

    // Tenant public storage — uploaded images stream from this tenant's own disk
    Route::get('/media/{path}', function (string $path) {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path);
    })->where('path', '.*')->name('media');

    // Public AI chatbot — rate limited, only when tenant has the chat module
    Route::post('/chat', [ChatController::class, 'send'])
        ->name('chat.send')
        ->middleware(['module:chat', 'throttle:10,1']);

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return view('app.dashboard', [
                'usersCount' => \App\Models\User::count(),
                'postsCount' => \App\Models\Post::count(),
                'viewsCount' => \App\Models\Post::sum('views'),
                'commentsCount' => \App\Models\Comment::where('approved', false)->count(),
                'employeesCount' => \App\Models\Employee::count(),
                'projectsCount' => \App\Models\Project::count(),
                'tasksCount' => \App\Models\Task::count(),
                'chatsCount' => \App\Models\ChatMessage::distinct('session_id')->count('session_id'),
                'recentPosts' => \App\Models\Post::with('user', 'category')->latest()->take(5)->get(),
                'recentComments' => \App\Models\Comment::with('post')->latest()->take(5)->get(),
            ]);
        })->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Chat history — staff can browse visitor conversations
        Route::get('/chats', [ChatController::class, 'index'])
            ->name('chats.index')
            ->middleware(['module:chat', 'access:chat', 'permission:chats.view']);

        // Team Chat — WhatsApp-style staff messaging (module: messaging)
        Route::middleware(['module:messaging', 'access:messaging'])->group(function () {
            Route::get('messages', [TeamChatController::class, 'index'])->name('messages.index');
            Route::post('messages/start', [TeamChatController::class, 'start'])->name('messages.start');
            Route::get('messages/conversations', [TeamChatController::class, 'conversations'])->name('messages.conversations');
            Route::get('messages/{conversation}/feed', [TeamChatController::class, 'messages'])->name('messages.feed');
            Route::post('messages/{conversation}/send', [TeamChatController::class, 'send'])->name('messages.send');
            Route::post('messages/{message}/delivered', [TeamChatController::class, 'delivered'])->name('messages.delivered');
            Route::post('messages/{conversation}/read', [TeamChatController::class, 'read'])->name('messages.read');
        });

        // Users — admin only
        Route::resource('users', UserController::class)
            ->except('show')
            ->middleware('role:admin');

        // Site settings — admin only (branding shown on the public site)
        Route::middleware('role:admin')->group(function () {
            Route::get('settings', [SiteSettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SiteSettingsController::class, 'update'])->name('settings.update');
        });

        // Posts — writers create/edit own, admins can publish & manage all
        Route::middleware(['module:posts', 'access:posts'])->group(function () {
            Route::get('posts', [PostController::class, 'index'])->name('posts.index')->middleware('permission:posts.view');
        Route::get('posts/create', [PostController::class, 'create'])->name('posts.create')->middleware('permission:posts.create');
        Route::post('posts', [PostController::class, 'store'])->name('posts.store')->middleware('permission:posts.create');
        Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit')->middleware('permission:posts.edit');
        Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update')->middleware('permission:posts.edit');
        Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy')->middleware('permission:posts.delete');

        // Categories & comments — blog management (posts/ prefix: plain /categories
        // belongs to the public site; same-method same-URI routes would overwrite it)
        Route::get('posts/categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('permission:posts.view');
        Route::post('posts/categories', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:posts.create');
        Route::put('posts/categories/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('permission:posts.edit');
        Route::delete('posts/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:posts.delete');
        Route::get('comments', [CommentController::class, 'index'])->name('comments.index')->middleware('permission:posts.view');
        Route::patch('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve')->middleware('permission:posts.edit');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy')->middleware('permission:posts.delete');
        });

        // Employees — hr + admin (EMS: departments, job roles, managers, project assignment)
        Route::middleware(['module:employees', 'access:employees'])->group(function () {
            Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index')->middleware('permission:employees.view');
        Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create')->middleware('permission:employees.create');
        Route::get('employees/template', [EmployeeController::class, 'template'])->name('employees.template')->middleware('permission:employees.view');
        Route::get('employees/import', fn () => view('app.employees.import'))->name('employees.import.form')->middleware('permission:employees.create');
        Route::post('employees/import', [EmployeeController::class, 'import'])->name('employees.import')->middleware('permission:employees.create');
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:employees.create');
        Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show')->middleware('permission:employees.view');
        Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:employees.edit');
        Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update')->middleware('permission:employees.edit');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('permission:employees.delete');

        // Departments
        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index')->middleware('permission:employees.view');
        Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store')->middleware('permission:employees.create');
        Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update')->middleware('permission:employees.edit');
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy')->middleware('permission:employees.delete');

        // Job Roles
        Route::get('job-roles', [JobRoleController::class, 'index'])->name('job-roles.index')->middleware('permission:employees.view');
        Route::post('job-roles', [JobRoleController::class, 'store'])->name('job-roles.store')->middleware('permission:employees.create');
        Route::put('job-roles/{jobRole}', [JobRoleController::class, 'update'])->name('job-roles.update')->middleware('permission:employees.edit');
        Route::delete('job-roles/{jobRole}', [JobRoleController::class, 'destroy'])->name('job-roles.destroy')->middleware('permission:employees.delete');

        // Managers
        Route::get('managers', [ManagerController::class, 'index'])->name('managers.index')->middleware('permission:employees.view');
        Route::post('managers', [ManagerController::class, 'store'])->name('managers.store')->middleware('permission:employees.create');
        Route::put('managers/{manager}', [ManagerController::class, 'update'])->name('managers.update')->middleware('permission:employees.edit');
        Route::delete('managers/{manager}', [ManagerController::class, 'destroy'])->name('managers.destroy')->middleware('permission:employees.delete');
        });

        // Projects — admin manages, members view
        Route::middleware(['module:projects', 'access:projects'])->group(function () {
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
    });

    require __DIR__.'/tenant-auth.php';
});
