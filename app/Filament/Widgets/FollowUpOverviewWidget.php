<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FollowUpOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        $query = Client::query()->visibleTo(auth()->user());

        return [
            Stat::make('متابعات اليوم', (clone $query)->whereDate('next_follow_up_at', today())->count()),
            Stat::make('خلال 7 أيام', (clone $query)->whereBetween('next_follow_up_at', [now()->startOfDay(), now()->addDays(7)->endOfDay()])->count())->color('warning'),
            Stat::make('متابعات متأخرة', (clone $query)->followUpOverdue()->count())->color('danger'),
        ];
    }
}
