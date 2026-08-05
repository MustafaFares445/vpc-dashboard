<?php

use App\Enums\ClientStatus;
use App\Enums\FinancialTransactionType;
use App\Enums\PaymentStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\FinancialTransaction;
use App\Models\Task;
use App\Models\User;
use App\Services\CompactReportService;

it('returns the compact operational and financial report', function () {
    $user = User::factory()->create();
    $client = Client::query()->create(['name' => 'Client', 'status' => ClientStatus::Active, 'assigned_to' => $user->id]);

    FinancialTransaction::query()->create(['type' => FinancialTransactionType::Income, 'date' => now(), 'amount' => 100, 'payment_status' => PaymentStatus::Paid]);
    ClientInteraction::query()->create(['client_id' => $client->id, 'user_id' => $user->id, 'contacted_at' => now(), 'note' => 'Called']);
    Task::query()->create(['title' => 'Done', 'assigned_to' => $user->id, 'status' => TaskStatus::Completed, 'priority' => TaskPriority::Medium, 'due_at' => now()]);

    $summary = app(CompactReportService::class)->summarize(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());

    expect($summary['income'])->toBe(100.0)
        ->and($summary['new_clients'])->toBe(1)
        ->and($summary['completed_tasks'])->toBe(1)
        ->and($summary['completed_follow_ups'])->toBe(1);
});
