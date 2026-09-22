<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\Pages\ViewTask;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('sets and clears the completion timestamp from the status', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $task = Task::query()->create([
        'title' => 'Complete report',
        'assigned_to' => $user->id,
        'priority' => TaskPriority::High,
        'status' => TaskStatus::Pending,
    ]);

    expect($task->completed_at)->toBeNull();
    $task->update(['status' => TaskStatus::Completed]);
    expect($task->fresh()->completed_at)->not->toBeNull();
    $task->update(['status' => TaskStatus::InProgress]);
    expect($task->fresh()->completed_at)->toBeNull();
});

it('identifies overdue tasks without storing an overdue status', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $overdue = Task::query()->create([
        'title' => 'Overdue', 'assigned_to' => $user->id,
        'priority' => TaskPriority::Medium, 'status' => TaskStatus::Pending,
        'due_at' => now()->subDay(),
    ]);

    $completed = Task::query()->create([
        'title' => 'Completed', 'assigned_to' => $user->id,
        'priority' => TaskPriority::Medium, 'status' => TaskStatus::Completed,
        'due_at' => now()->subDay(),
    ]);

    expect($overdue->is_overdue)->toBeTrue()
        ->and($completed->is_overdue)->toBeFalse()
        ->and(Task::query()->overdue()->pluck('id')->all())->toBe([$overdue->id]);
});

it('allows an employee with view permission to open assigned task details', function () {
    Role::findByName('employee', 'web')->syncPermissions(['tasks.view']);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $employee = User::factory()->create();
    $employee->assignRole('employee');

    $task = Task::query()->create([
        'title' => 'Prepare customer quotation',
        'description' => 'Review the customer requirements and prepare the final quotation.',
        'assigned_to' => $employee->id,
        'priority' => TaskPriority::High,
        'status' => TaskStatus::Pending,
    ]);

    $this->actingAs($employee);

    expect($employee->can('tasks.view'))->toBeTrue()
        ->and($employee->can('tasks.update'))->toBeFalse()
        ->and($employee->can('view', $task))->toBeTrue();

    Livewire::test(ViewTask::class, ['record' => $task->getRouteKey()])
        ->assertOk()
        ->assertSee('Prepare customer quotation')
        ->assertSee('Review the customer requirements and prepare the final quotation.')
        ->assertSee('الوصف والشرح');
});
