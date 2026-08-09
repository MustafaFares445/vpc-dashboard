<?php

namespace App\Filament;

use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'الرئيسية';

    protected static bool $shouldRegisterNavigation = false;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('فترة الملخص المالي')->columns(2)->schema([
                DatePicker::make('startDate')->label('من')->default(now()->startOfMonth()),
                DatePicker::make('endDate')->label('إلى')->default(now()->endOfMonth())->afterOrEqual('startDate'),
            ]),
        ]);
    }

    public function getColumns(): int|array
    {
        return ['md' => 2, 'xl' => 3];
    }
}
