<?php

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('syncs the latest interaction dates to the client', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');
    $client = Client::query()->create(['name' => 'Acme', 'status' => ClientStatus::Active, 'assigned_to' => $user->id, 'created_by' => $user->id]);
    ClientInteraction::query()->create([
        'client_id' => $client->id,
        'user_id' => $user->id,
        'contacted_at' => '2026-08-01 10:00:00',
        'note' => 'Initial call',
        'next_follow_up_at' => '2026-08-10 10:00:00',
    ]);
    expect($client->fresh()->last_contact_at->toDateTimeString())->toBe('2026-08-01 10:00:00')
        ->and($client->fresh()->next_follow_up_at->toDateTimeString())->toBe('2026-08-10 10:00:00');
});

it('stores the employee responsible for a follow-up', function () {
    $user = User::factory()->create();
    $client = Client::query()->create(['name' => 'Acme', 'status' => ClientStatus::Active, 'created_by' => $user->id]);
    $employee = Employee::query()->create(['name' => 'Sales Employee', 'is_active' => true]);

    $interaction = ClientInteraction::query()->create([
        'client_id' => $client->id,
        'user_id' => $user->id,
        'employee_id' => $employee->id,
        'contacted_at' => '2026-08-01 10:00:00',
        'note' => 'Follow-up call',
    ]);

    expect($interaction->employee->is($employee))->toBeTrue()
        ->and($interaction->getMedia('attachments'))->toHaveCount(0);
});

it('scopes employees to their assigned clients', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');
    $other = User::factory()->create();
    $other->assignRole('employee');
    Client::query()->create(['name' => 'Visible', 'status' => ClientStatus::Active, 'assigned_to' => $employee->id]);
    Client::query()->create(['name' => 'Hidden', 'status' => ClientStatus::Active, 'assigned_to' => $other->id]);
    expect(Client::query()->visibleTo($employee)->pluck('name')->all())->toBe(['Visible']);
});
