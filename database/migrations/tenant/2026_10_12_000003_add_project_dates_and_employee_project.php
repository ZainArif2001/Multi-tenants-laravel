<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'start_date')) {
                $table->date('start_date')->nullable()->after('status');
            }
            if (! Schema::hasColumn('projects', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        // Works_On — employees assigned to projects with assigned hours (M:N)
        if (! Schema::hasTable('employee_project')) {
            Schema::create('employee_project', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->foreignId('project_id')->constrained()->cascadeOnDelete();
                $table->unsignedSmallInteger('assigned_hours')->nullable();
                $table->timestamps();
                $table->unique(['employee_id', 'project_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_project');
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
