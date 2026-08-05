<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        $query = Task::query()->visibleTo(auth()->user());

        return [
            Stat::make('المهام المفتوحة', (clone $query)->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])->count()),
            Stat::make('المهام المتأخرة', (clone $query)->overdue()->count())->color('danger'),
            Stat::make('المستحقة اليوم', (clone $query)->whereDate('due_at', today())->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])->count())->color('warning'),
            Stat::make('المكتملة هذا الشهر', (clone $query)->where('status', TaskStatus::Completed->value)->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])->count())->color('success'),
        ];
    }
}
