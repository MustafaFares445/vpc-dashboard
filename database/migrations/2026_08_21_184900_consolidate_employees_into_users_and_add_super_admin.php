<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_super_admin')->default(false)->after('is_active')->index();
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('job_title')->nullable()->after('phone');
            $table->date('hire_date')->nullable()->after('job_title');
            $table->text('notes')->nullable()->after('hire_date');
        });

        $adminRoleId = DB::table('roles')
            ->where('name', 'admin')
            ->where('guard_name', 'web')
            ->value('id');

        if ($adminRoleId) {
            $adminIds = DB::table('model_has_roles')
                ->where('role_id', $adminRoleId)
                ->where('model_type', User::class)
                ->pluck('model_id');

            DB::table('users')->whereIn('id', $adminIds)->update(['is_super_admin' => true]);
        }

        if (! Schema::hasTable('employees')) {
            return;
        }

        $employeeRoleId = DB::table('roles')
            ->where('name', 'employee')
            ->where('guard_name', 'web')
            ->value('id');

        $employeeToUser = [];

        foreach (DB::table('employees')->orderBy('id')->get() as $employee) {
            $email = $employee->email ?: "employee-{$employee->id}@local.invalid";
            $userId = DB::table('users')->where('email', $email)->value('id');

            if (! $userId) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $employee->name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(64)),
                    'is_active' => (bool) $employee->is_active && $employee->deleted_at === null,
                    'is_super_admin' => false,
                    'phone' => $employee->phone,
                    'job_title' => $employee->job_title,
                    'hire_date' => $employee->hire_date,
                    'notes' => $employee->notes,
                    'created_at' => $employee->created_at ?? now(),
                    'updated_at' => $employee->updated_at ?? now(),
                ]);
            } else {
                DB::table('users')->where('id', $userId)->update([
                    'phone' => $employee->phone,
                    'job_title' => $employee->job_title,
                    'hire_date' => $employee->hire_date,
                    'notes' => $employee->notes,
                    'updated_at' => now(),
                ]);
            }

            if ($employeeRoleId && ! DB::table('users')->where('id', $userId)->value('is_super_admin')) {
                DB::table('model_has_roles')->updateOrInsert([
                    'role_id' => $employeeRoleId,
                    'model_type' => User::class,
                    'model_id' => $userId,
                ]);
            }

            $employeeToUser[$employee->id] = $userId;
        }

        if (Schema::hasColumn('client_interactions', 'employee_id')) {
            Schema::table('client_interactions', function (Blueprint $table): void {
                $table->dropForeign(['employee_id']);
            });

            foreach ($employeeToUser as $employeeId => $userId) {
                DB::table('client_interactions')
                    ->where('employee_id', $employeeId)
                    ->update(['employee_id' => $userId]);
            }

            DB::table('client_interactions')
                ->whereNotNull('employee_id')
                ->whereNotIn('employee_id', array_values($employeeToUser))
                ->update(['employee_id' => null]);

            Schema::table('client_interactions', function (Blueprint $table): void {
                $table->foreign('employee_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        Schema::dropIfExists('employees');
    }

    public function down(): void
    {
        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable()->unique();
                $table->string('job_title')->nullable();
                $table->date('hire_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['is_active', 'name']);
            });
        }

        foreach (DB::table('users')->where('is_super_admin', false)->orderBy('id')->get() as $user) {
            DB::table('employees')->updateOrInsert(
                ['id' => $user->id],
                [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => str_ends_with($user->email, '@local.invalid') ? null : $user->email,
                    'job_title' => $user->job_title,
                    'hire_date' => $user->hire_date,
                    'is_active' => $user->is_active,
                    'notes' => $user->notes,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'deleted_at' => null,
                ],
            );
        }

        if (Schema::hasColumn('client_interactions', 'employee_id')) {
            Schema::table('client_interactions', function (Blueprint $table): void {
                $table->dropForeign(['employee_id']);
                $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['is_super_admin', 'phone', 'job_title', 'hire_date', 'notes']);
        });
    }
};
