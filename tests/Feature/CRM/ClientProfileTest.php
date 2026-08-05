<?php

use App\Enums\ClientStatus;
use App\Enums\InvoiceStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Resources\Clients\RelationManagers\InteractionsRelationManager;
use App\Filament\Resources\Clients\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\Clients\RelationManagers\TasksRelationManager;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\Invoice;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('renders the complete client profile for administrators', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $client = Client::query()->create([
        'name' => 'Acme Medical',
        'company_name' => 'Acme',
        'status' => ClientStatus::Active,
        'assigned_to' => $admin->id,
        'created_by' => $admin->id,
    ]);

    ClientInteraction::query()->create([
        'client_id' => $client->id,
        'user_id' => $admin->id,
        'contacted_at' => now(),
        'note' => 'Discussed the next order.',
    ]);

    Task::query()->create([
        'title' => 'Prepare quotation',
        'assigned_to' => $admin->id,
        'client_id' => $client->id,
        'priority' => TaskPriority::High,
        'status' => TaskStatus::Pending,
        'created_by' => $admin->id,
    ]);

    Invoice::query()->create([
        'invoice_number' => 'INV-CLIENT-001',
        'client_id' => $client->id,
        'issue_date' => now()->toDateString(),
        'status' => InvoiceStatus::PartiallyPaid,
        'subtotal' => 100,
        'total' => 100,
        'paid_amount' => 40,
        'created_by' => $admin->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
        ->assertOk()
        ->assertSee('Acme Medical')
        ->assertSee('إجمالي الفواتير')
        ->assertSee('الرصيد غير المدفوع')
        ->assertSeeLivewire(InteractionsRelationManager::class)
        ->set('activeRelationManager', 'tasks')
        ->assertSeeLivewire(TasksRelationManager::class)
        ->assertSee('Prepare quotation')
        ->set('activeRelationManager', 'invoices')
        ->assertSeeLivewire(InvoicesRelationManager::class)
        ->assertSee('INV-CLIENT-001');
});

it('scopes client profile data for employees', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');

    $otherEmployee = User::factory()->create();
    $otherEmployee->assignRole('employee');

    $client = Client::query()->create([
        'name' => 'Assigned Client',
        'status' => ClientStatus::Active,
        'assigned_to' => $employee->id,
        'created_by' => $employee->id,
    ]);

    Task::query()->create([
        'title' => 'Visible employee task',
        'assigned_to' => $employee->id,
        'client_id' => $client->id,
        'priority' => TaskPriority::Medium,
        'status' => TaskStatus::Pending,
        'created_by' => $employee->id,
    ]);

    Task::query()->create([
        'title' => 'Hidden employee task',
        'assigned_to' => $otherEmployee->id,
        'client_id' => $client->id,
        'priority' => TaskPriority::Medium,
        'status' => TaskStatus::Pending,
        'created_by' => $otherEmployee->id,
    ]);

    $this->actingAs($employee);

    Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
        ->assertOk()
        ->assertSeeLivewire(InteractionsRelationManager::class)
        ->assertDontSee('إجمالي الفواتير')
        ->assertDontSee('الرصيد غير المدفوع')
        ->set('activeRelationManager', 'tasks')
        ->assertSeeLivewire(TasksRelationManager::class)
        ->assertSee('Visible employee task')
        ->assertDontSee('Hidden employee task');

    expect(Gate::forUser($employee)->allows('viewAny', Invoice::class))->toBeFalse();
});
