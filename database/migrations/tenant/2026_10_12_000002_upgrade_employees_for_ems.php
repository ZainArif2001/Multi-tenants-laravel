<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL ALTERs aren't transactional — guard every step so this
        // migration can safely re-run after a partial failure.
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'first_name')) {
                $table->string('first_name')->nullable()->after('id');
            }
            if (! Schema::hasColumn('employees', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('employees', 'salary')) {
                $table->decimal('salary', 10, 2)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('employees', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('salary');
            }
            if (! Schema::hasColumn('employees', 'job_title')) {
                $table->string('job_title')->nullable()->after('hire_date');
            }
            if (! Schema::hasColumn('employees', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('job_title')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('employees', 'job_role_id')) {
                $table->foreignId('job_role_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('employees', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->after('job_role_id')->constrained()->nullOnDelete();
            }
        });

        // Migrate legacy data while old columns still exist (idempotent —
        // values are deterministic from the source columns).
        if (Schema::hasColumn('employees', 'department')) {
            foreach (DB::table('employees')->get() as $emp) {
                $departmentId = null;
                if (filled($emp->department)) {
                    $departmentId = DB::table('departments')->where('name', $emp->department)->value('id')
                        ?? DB::table('departments')->insertGetId([
                            'name' => $emp->department,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                DB::table('employees')->where('id', $emp->id)->update([
                    'first_name' => Str::of($emp->name)->before(' ')->toString() ?: $emp->name,
                    'last_name' => Str::of($emp->name)->contains(' ') ? Str::of($emp->name)->after(' ')->toString() : null,
                    'job_title' => $emp->position,
                    'hire_date' => $emp->joined_at,
                    'department_id' => $departmentId,
                ]);
            }
        }

        $drop = collect(['department', 'position', 'joined_at'])
            ->filter(fn ($col) => Schema::hasColumn('employees', $col))
            ->all();
        if ($drop) {
            Schema::table('employees', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->date('joined_at')->nullable();
            $table->dropConstrainedForeignId('manager_id');
            $table->dropConstrainedForeignId('job_role_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['first_name', 'last_name', 'salary', 'hire_date', 'job_title']);
        });
    }
};
