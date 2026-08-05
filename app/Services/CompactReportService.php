<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\Task;

class CompactReportService
{
    public function summarize(mixed $from, mixed $to): array
    {
        return [
            ...app(FinancialSummaryService::class)->summarize($from, $to),
            'new_clients' => Client::query()->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count(),
            'completed_tasks' => Task::query()->where('status', TaskStatus::Completed->value)->whereDate('completed_at', '>=', $from)->whereDate('completed_at', '<=', $to)->count(),
            'overdue_tasks' => Task::query()->overdue()->whereDate('due_at', '>=', $from)->whereDate('due_at', '<=', $to)->count(),
            'completed_follow_ups' => ClientInteraction::query()->whereDate('contacted_at', '>=', $from)->whereDate('contacted_at', '<=', $to)->count(),
        ];
    }
}
