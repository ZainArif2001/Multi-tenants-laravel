<?php

use Illuminate\Support\Facades\Route;
use App\Models\Post;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Message;

/*
|--------------------------------------------------------------------------
| Tenant API — authenticated with Sanctum personal access tokens.
| Bearer token is created on the tenant Profile page (admin).
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('v1')->name('api.')->group(function () {
    Route::get('/me', fn ($r) => $r->user()->only('id', 'name', 'email', 'allowed_modules'));

    Route::middleware('module:posts')->group(function () {
        Route::get('/posts', fn () => Post::latest()->paginate(20));
        Route::get('/posts/{post}', fn (Post $post) => $post);
    });

    Route::middleware('module:employees')->group(function () {
        Route::get('/employees', fn () => Employee::latest()->paginate(20));
        Route::get('/employees/{employee}', fn (Employee $employee) => $employee);
    });

    Route::middleware('module:projects')->group(function () {
        Route::get('/projects', fn () => Project::latest()->paginate(20));
    });

    Route::middleware('module:messaging')->group(function () {
        Route::get('/messages', fn () => Message::latest()->paginate(20));
    });
});
