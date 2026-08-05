<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait LogsModelActivity
{
    public static function bootLogsModelActivity(): void
    {
        static::created(fn (Model $model) => $model->writeAuditLog('created'));
        static::updated(fn (Model $model) => $model->writeAuditLog('updated'));
        static::deleted(fn (Model $model) => $model->writeAuditLog('deleted'));
    }

    protected function writeAuditLog(string $event): void
    {
        if (! config('app.audit_enabled', true)) {
            return;
        }

        $request = request();

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'old_values' => $event === 'created' ? null : $this->getOriginal(),
            'new_values' => $event === 'deleted' ? null : $this->getAttributes(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
