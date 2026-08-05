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
        ->assertSeeLivewire(InteractionsRelationManager::class)
        ->assertSeeLivewire(TasksRelationManager::class)
        ->assertSeeLivewire(InvoicesRelationManager::class);
});

it('keeps invoice data hidden from employees on the client profile', function () {
    $employee = User::factory()->create();
    $employee->assignRole('employee');

    $client = Client::query()->create([
        'name' => 'Assigned Client',
        'status' => ClientStatus::Active,
        'assigned_to' => $employee->id,
        'created_by' => $employee->id,
    ]);

    $this->actingAs($employee);

    Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
        ->assertOk()
        ->assertSeeLivewire(InteractionsRelationManager::class)
        ->assertSeeLivewire(TasksRelationManager::class)
        ->assertDontSeeLivewire(InvoicesRelationManager::class)
        ->assertDontSee('إجمالي الفواتير')
        ->assertDontSee('الرصيد غير المدفوع');
});
