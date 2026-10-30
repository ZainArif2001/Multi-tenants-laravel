<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tenant activity log — records create/update/delete on key models.
        $models = [
            \App\Models\Post::class,
            \App\Models\Employee::class,
            \App\Models\Project::class,
            \App\Models\Task::class,
            \App\Models\User::class,
            \App\Models\Conversation::class,
        ];

        foreach (['created', 'updated', 'deleted'] as $event) {
            foreach ($models as $model) {
                $model::{$event}(function ($subject) use ($event) {
                    // Only inside an initialized tenant context, once the table exists
                    if (! function_exists('tenant') || ! tenant()) {
                        return;
                    }
                    static $hasTable = null;
                    $hasTable ??= \Illuminate\Support\Facades\Schema::hasTable('activity_logs');
                    if (! $hasTable) {
                        return;
                    }

                    $label = class_basename($subject);
                    $name = $subject->name
                        ?? $subject->title
                        ?? trim(($subject->first_name ?? '') . ' ' . ($subject->last_name ?? ''))
                        ?: "#{$subject->getKey()}";

                    // Actor must exist in THIS database — during tenant seeding
                    // the authed central admin has no row in the tenant's users
                    // table, and the FK would blow up the whole job pipeline.
                    $actorId = auth()->id();
                    $actorExists = $actorId
                        && \App\Models\User::where('id', $actorId)->exists();

                    \App\Models\ActivityLog::create([
                        'user_id' => $actorExists ? $actorId : null,
                        'action' => $event,
                        'subject_type' => $label,
                        'subject_id' => $subject->getKey(),
                        'description' => sprintf('%s %s "%s"', $label, $event, $name),
                    ]);
                });
            }
        }
    }
}
