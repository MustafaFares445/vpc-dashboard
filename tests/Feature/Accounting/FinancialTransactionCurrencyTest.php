<?php

use App\Enums\Currency;
use App\Enums\FinancialTransactionType;
use App\Enums\PaymentStatus;
use App\Models\FinancialTransaction;
use App\Models\User;

it('stores the selected currency on a financial transaction', function () {
    $transaction = FinancialTransaction::query()->create([
        'type' => FinancialTransactionType::Income,
        'date' => '2026-08-13',
        'amount' => 125000,
        'currency' => Currency::SYP,
        'payment_status' => PaymentStatus::Paid,
    ]);

    expect($transaction->fresh()->currency)->toBe(Currency::SYP);
});

it('defaults existing-style financial transactions to USD', function () {
    $transaction = FinancialTransaction::query()->create([
        'type' => FinancialTransactionType::Expense,
        'date' => '2026-08-13',
        'amount' => 25,
        'payment_status' => PaymentStatus::Paid,
    ]);

    expect($transaction->fresh()->currency)->toBe(Currency::USD);
});

it('can link a financial transaction to an employee user', function () {
    $employee = User::factory()->create(['is_super_admin' => false]);

    $transaction = FinancialTransaction::query()->create([
        'type' => FinancialTransactionType::Expense,
        'date' => '2026-09-17',
        'amount' => 75,
        'payment_status' => PaymentStatus::Pending,
        'employee_id' => $employee->id,
    ]);

    expect($transaction->fresh()->employee->is($employee))->toBeTrue();
});
