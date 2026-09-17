<?php

use App\Enums\FinancialTransactionType;
use App\Enums\PaymentStatus;
use App\Models\FinancialTransaction;
use App\Services\FinancialSummaryService;

it('calculates profit and net profit for a date range', function () {
    FinancialTransaction::query()->create(['type' => FinancialTransactionType::Income, 'date' => '2026-08-01', 'amount' => 1000, 'payment_status' => PaymentStatus::Paid]);
    FinancialTransaction::query()->create(['type' => FinancialTransactionType::Cost, 'date' => '2026-08-02', 'amount' => 250, 'payment_status' => PaymentStatus::Paid]);
    FinancialTransaction::query()->create(['type' => FinancialTransactionType::Expense, 'date' => '2026-08-03', 'amount' => 150, 'payment_status' => PaymentStatus::Paid]);
    FinancialTransaction::query()->create(['type' => FinancialTransactionType::Income, 'date' => '2026-07-01', 'amount' => 5000, 'payment_status' => PaymentStatus::Paid]);

    $summary = app(FinancialSummaryService::class)->summarize('2026-08-01', '2026-08-31');

    expect($summary)->toMatchArray([
        'income' => 1000.0, 'costs' => 250.0, 'expenses' => 150.0,
        'profit' => 750.0, 'net_profit' => 600.0, 'net_profit_percentage' => 60.0,
    ]);
});

it('filters financial summaries by payment status', function () {
    FinancialTransaction::query()->create(['type' => FinancialTransactionType::Income, 'date' => '2026-09-01', 'amount' => 100, 'payment_status' => PaymentStatus::Paid]);
    FinancialTransaction::query()->create(['type' => FinancialTransactionType::Income, 'date' => '2026-09-02', 'amount' => 300, 'payment_status' => PaymentStatus::Pending]);
    FinancialTransaction::query()->create(['type' => FinancialTransactionType::Expense, 'date' => '2026-09-03', 'amount' => 50, 'payment_status' => PaymentStatus::Pending]);

    $summary = app(FinancialSummaryService::class)->summarize('2026-09-01', '2026-09-30', PaymentStatus::Pending);

    expect($summary)->toMatchArray([
        'income' => 300.0,
        'expenses' => 50.0,
        'costs' => 0.0,
        'profit' => 300.0,
        'net_profit' => 250.0,
    ]);
});

it('returns a zero percentage when income is zero', function () {
    expect(app(FinancialSummaryService::class)->summarize()['net_profit_percentage'])->toBe(0.0);
});
