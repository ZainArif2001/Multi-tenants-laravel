<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Department — one department has many employees
        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }

        // Job Role — the employee's functional role (Developer, HR Executive...)
        if (! Schema::hasTable('job_roles')) {
            Schema::create('job_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Manager — leads employees
        if (! Schema::hasTable('managers')) {
            Schema::create('managers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('managers');
        Schema::dropIfExists('job_roles');
        Schema::dropIfExists('departments');
    }
};
