<?php

namespace App\Filament\Widgets;

use App\Services\FinancialSummaryService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverviewWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getStats(): array
    {
        $summary = app(FinancialSummaryService::class)->summarize(
            $this->pageFilters['startDate'] ?? null,
            $this->pageFilters['endDate'] ?? null,
        );

        return [
            Stat::make('الإيرادات', number_format($summary['income'], 2))->color('success'),
            Stat::make('المصاريف', number_format($summary['expenses'], 2))->color('danger'),
            Stat::make('التكاليف', number_format($summary['costs'], 2))->color('warning'),
            Stat::make('الربح', number_format($summary['profit'], 2)),
            Stat::make('صافي الربح', number_format($summary['net_profit'], 2))
                ->description($summary['net_profit_percentage'].'%')
                ->color($summary['net_profit'] >= 0 ? 'success' : 'danger'),
        ];
    }
}
