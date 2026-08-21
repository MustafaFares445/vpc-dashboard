<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use LogsModelActivity;
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'assigned_to', 'client_id', 'due_at',
        'priority', 'status', 'reference', 'notes', 'completed_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $task): void {
            if ($task->status === TaskStatus::Completed && ! $task->completed_at) {
                $task->completed_at = now();
            }

            if ($task->status !== TaskStatus::Completed) {
                $task->completed_at = null;
            }
        });
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->can('tasks.manage') ? $query : $query->where('assigned_to', $user->getKey());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value]);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at?->isPast()
            && ! in_array($this->status, [TaskStatus::Completed, TaskStatus::Cancelled], true);
    }
}
