<?php

namespace App\Models;

use App\Enums\ContactMethod;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientInteraction extends Model
{
    use HasFactory;
    use LogsModelActivity;

    protected $fillable = [
        'client_id',
        'user_id',
        'contacted_at',
        'contact_method',
        'note',
        'next_follow_up_at',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'contact_method' => ContactMethod::class,
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (self $interaction) => $interaction->syncClientDates());
        static::deleted(fn (self $interaction) => $interaction->syncClientDates());
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    private function syncClientDates(): void
    {
        $client = $this->client()->first();

        if (! $client) {
            return;
        }

        $latest = $client->interactions()
            ->latest('contacted_at')
            ->latest('id')
            ->first();

        $client->updateQuietly([
            'last_contact_at' => $latest?->contacted_at,
            'next_follow_up_at' => $latest?->next_follow_up_at,
        ]);
    }
}
