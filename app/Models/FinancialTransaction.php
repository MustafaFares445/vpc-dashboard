<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\FinancialTransactionType;
use App\Enums\PaymentStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FinancialTransaction extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsModelActivity;
    use SoftDeletes;

    protected $fillable = ['type', 'date', 'amount', 'currency', 'payment_status', 'description', 'client_id', 'invoice_id', 'created_by'];

    protected function casts(): array
    {
        return [
            'type' => FinancialTransactionType::class,
            'date' => 'date',
            'amount' => 'decimal:2',
            'currency' => Currency::class,
            'payment_status' => PaymentStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeBetweenDates(Builder $query, mixed $from = null, mixed $to = null): Builder
    {
        return $query
            ->when($from, fn (Builder $query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('date', '<=', $to));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp']);
    }
}
