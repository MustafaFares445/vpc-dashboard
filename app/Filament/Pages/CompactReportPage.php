<?php

namespace App\Filament\Pages;

use App\Services\CompactReportService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CompactReportPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'التقرير المختصر';

    protected static string|UnitEnum|null $navigationGroup = 'المحاسبة';

    protected static ?string $slug = 'compact-report';

    protected string $view = 'filament.pages.compact-report-page';

    public string $from;

    public string $to;

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->endOfMonth()->toDateString();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function summary(): array
    {
        return app(CompactReportService::class)->summarize($this->from, $this->to);
    }

    public function getHeading(): string
    {
        return 'التقرير المختصر';
    }
}
