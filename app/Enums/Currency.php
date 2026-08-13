<?php

namespace App\Enums;

enum Currency: string
{
    case USD = 'USD';
    case SYP = 'SYP';

    public function label(): string
    {
        return match ($this) {
            self::USD => 'دولار أمريكي (USD)',
            self::SYP => 'ليرة سورية (SYP)',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $currency): array => [$currency->value => $currency->label()])->all();
    }
}
