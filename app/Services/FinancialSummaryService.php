<?php

namespace App\Services;

use App\Enums\FinancialTransactionType;
use App\Models\FinancialTransaction;
use Illuminate\Database\Eloquent\Builder;

class FinancialSummaryService
{
    public function summarize(mixed $from = null, mixed $to = null): array
    {
        $query = FinancialTransaction::query()->betweenDates($from, $to);
        $income = $this->sumForType(clone $query, FinancialTransactionType::Income);
        $expenses = $this->sumForType(clone $query, FinancialTransactionType::Expense);
        $costs = $this->sumForType(clone $query, FinancialTransactionType::Cost);
        $profit = round($income - $costs, 2);
        $netProfit = round($income - $costs - $expenses, 2);

        return [
            'income' => $income,
            'expenses' => $expenses,
            'costs' => $costs,
            'profit' => $profit,
            'net_profit' => $netProfit,
            'net_profit_percentage' => $income > 0 ? round(($netProfit / $income) * 100, 2) : 0.0,
        ];
    }

    private function sumForType(Builder $query, FinancialTransactionType $type): float
    {
        return round((float) $query->where('type', $type->value)->sum('amount'), 2);
    }
}
