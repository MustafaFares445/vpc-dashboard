<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'غير مدفوعة',
            self::PartiallyPaid => 'مدفوعة جزئيًا',
            self::Paid => 'مدفوعة',
        };
    }

    public static function fromAmounts(float $total, float $paidAmount): self
    {
        if ($paidAmount <= 0) {
            return self::Unpaid;
        }

        return $paidAmount >= $total ? self::Paid : self::PartiallyPaid;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])->all();
    }
}
