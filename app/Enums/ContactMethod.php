<?php

namespace App\Enums;

enum ContactMethod: string
{
    case Phone = 'phone';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Meeting = 'meeting';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Phone => 'هاتف',
            self::WhatsApp => 'واتساب',
            self::Email => 'بريد إلكتروني',
            self::Meeting => 'اجتماع',
            self::Other => 'أخرى',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $method): array => [$method->value => $method->label()])
            ->all();
    }
}
