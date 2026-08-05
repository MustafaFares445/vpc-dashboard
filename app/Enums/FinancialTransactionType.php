<?php

namespace App\Enums;

enum FinancialTransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Cost = 'cost';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'إيراد',
            self::Expense => 'مصروف',
            self::Cost => 'تكلفة',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])->all();
    }
}
