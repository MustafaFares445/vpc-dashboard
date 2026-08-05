<?php

namespace App\Models;

use App\Enums\ClientStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Client extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsModelActivity;
    use SoftDeletes;

    protected $fillable = ['name', 'company_name', 'email', 'phone', 'status', 'assigned_to', 'last_contact_at', 'next_follow_up_at', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['status' => ClientStatus::class, 'last_contact_at' => 'datetime', 'next_follow_up_at' => 'datetime'];
    }

    public function assignedUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function interactions(): HasMany { return $this->hasMany(ClientInteraction::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function financialTransactions(): HasMany { return $this->hasMany(FinancialTransaction::class); }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isAdmin() ? $query : $query->where('assigned_to', $user->getKey());
    }

    public function scopeFollowUpOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('next_follow_up_at')->where('next_follow_up_at', '<', now());
    }
}
