<?php

use App\Enums\ClientStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('prevents employees from changing client master data', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');
    $client = Client::query()->create(['name' => 'Assigned client', 'status' => ClientStatus::Active, 'assigned_to' => $employee->id]);

    expect($employee->can('view', $client))->toBeTrue()
        ->and($employee->can('update', $client))->toBeFalse();
});

it('allows employees to update only their assigned task through policy scope', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');
    $other = User::factory()->create();
    $other->assignRole('employee');

    $assigned = Task::query()->create(['title' => 'Assigned', 'assigned_to' => $employee->id, 'priority' => TaskPriority::Medium, 'status' => TaskStatus::Pending]);
    $hidden = Task::query()->create(['title' => 'Hidden', 'assigned_to' => $other->id, 'priority' => TaskPriority::Medium, 'status' => TaskStatus::Pending]);

    expect($employee->can('create', Task::class))->toBeFalse()
        ->and($employee->can('update', $assigned))->toBeTrue()
        ->and($employee->can('update', $hidden))->toBeFalse();
});

it('keeps audit logs restricted to roles with the permission', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $employee = User::factory()->create();
    $employee->assignRole('employee');

    expect($admin->can('viewAny', AuditLog::class))->toBeTrue()
        ->and($employee->can('viewAny', AuditLog::class))->toBeFalse();
});

it('does not allow deleting the final active super administrator', function () {
    $superAdmin = User::factory()->create(['is_super_admin' => true]);
    $superAdmin->assignRole('admin');

    expect($superAdmin->can('delete', $superAdmin))->toBeFalse();

    $secondSuperAdmin = User::factory()->create(['is_super_admin' => true]);
    $secondSuperAdmin->assignRole('admin');

    expect($superAdmin->can('delete', $secondSuperAdmin))->toBeTrue();
});
