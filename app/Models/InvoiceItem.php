<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'quantity', 'unit_price', 'line_total', 'sort_order'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'line_total' => 'decimal:2', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $item->line_total = round((float) $item->quantity * (float) $item->unit_price, 2);
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
