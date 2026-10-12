<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    protected $fillable = [
        'name', 'first_name', 'last_name', 'email', 'phone',
        'salary', 'hire_date', 'job_title',
        'department_id', 'job_role_id', 'manager_id',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'hire_date' => 'date',
    ];

    protected static function booted(): void
    {
        // Keep `name` in sync — display pages (team, AI context) read it
        static::saving(function (Employee $employee) {
            if (filled($employee->first_name)) {
                $employee->name = trim($employee->first_name.' '.$employee->last_name);
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('assigned_hours')
            ->withTimestamps();
    }
}
