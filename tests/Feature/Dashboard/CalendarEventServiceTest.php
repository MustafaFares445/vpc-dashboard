<?php

use App\Enums\ClientStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use App\Services\CalendarEventService;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('returns only calendar events assigned to an employee', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');
    $other = User::factory()->create();
    $other->assignRole('employee');

    Client::query()->create(['name' => 'Visible client', 'status' => ClientStatus::Active, 'assigned_to' => $employee->id, 'next_follow_up_at' => now()->addDay()]);
    Client::query()->create(['name' => 'Hidden client', 'status' => ClientStatus::Active, 'assigned_to' => $other->id, 'next_follow_up_at' => now()->addDay()]);
    Task::query()->create(['title' => 'Visible task', 'assigned_to' => $employee->id, 'status' => TaskStatus::Pending, 'priority' => TaskPriority::Medium, 'due_at' => now()->addDay()]);

    $events = app(CalendarEventService::class)->forRange($employee, now()->startOfDay(), now()->addWeek()->endOfDay());

    expect($events->pluck('title')->all())
        ->toContain('متابعة: Visible client', 'مهمة: Visible task')
        ->not->toContain('متابعة: Hidden client');
});
