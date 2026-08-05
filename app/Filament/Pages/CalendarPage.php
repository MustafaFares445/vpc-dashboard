<?php

namespace App\Filament\Pages;

use App\Services\CalendarEventService;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CalendarPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static ?string $navigationLabel = 'التقويم';
    protected static string|UnitEnum|null $navigationGroup = 'الرئيسية';
    protected static ?string $slug = 'calendar';
    protected string $view = 'filament.pages.calendar-page';

    public string $month;

    public function mount(): void { $this->month = now()->format('Y-m'); }
    public function previousMonth(): void { $this->month = CarbonImmutable::createFromFormat('Y-m', $this->month)->subMonth()->format('Y-m'); }
    public function nextMonth(): void { $this->month = CarbonImmutable::createFromFormat('Y-m', $this->month)->addMonth()->format('Y-m'); }
    public function currentMonth(): void { $this->month = now()->format('Y-m'); }

    public function calendarDays(): array
    {
        $month = CarbonImmutable::createFromFormat('Y-m', $this->month)->startOfMonth();
        $start = $month->startOfWeek(CarbonImmutable::SUNDAY)->startOfDay();
        $end = $start->addDays(41)->endOfDay();
        $events = app(CalendarEventService::class)->forRange(auth()->user(), $start, $end)->groupBy('date');

        return collect(range(0, 41))->map(function (int $offset) use ($start, $month, $events): array {
            $date = $start->addDays($offset);
            return [
                'date' => $date,
                'is_current_month' => $date->month === $month->month,
                'is_today' => $date->isToday(),
                'events' => $events->get($date->toDateString(), collect()),
            ];
        })->all();
    }

    public function getHeading(): string
    {
        return 'التقويم - '.CarbonImmutable::createFromFormat('Y-m', $this->month)->locale('ar')->translatedFormat('F Y');
    }
}
