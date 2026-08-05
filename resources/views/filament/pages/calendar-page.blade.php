<x-filament-panels::page>
    <div class="space-y-4" dir="rtl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-2">
                <x-filament::button wire:click="previousMonth" color="gray">الشهر السابق</x-filament::button>
                <x-filament::button wire:click="currentMonth" color="gray">اليوم</x-filament::button>
                <x-filament::button wire:click="nextMonth" color="gray">الشهر التالي</x-filament::button>
            </div>
            <div class="text-sm text-gray-500">مواعيد متابعة العملاء والمواعيد النهائية للمهام</div>
        </div>

        <div class="grid grid-cols-7 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            @foreach (['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'] as $dayName)
                <div class="border-b border-gray-200 p-2 text-center text-sm font-semibold dark:border-white/10">{{ $dayName }}</div>
            @endforeach

            @foreach ($this->calendarDays() as $day)
                <div wire:key="calendar-day-{{ $day['date']->toDateString() }}"
                     @class([
                        'min-h-32 border-b border-l border-gray-200 p-2 dark:border-white/10',
                        'bg-gray-50 text-gray-400 dark:bg-gray-950' => ! $day['is_current_month'],
                        'ring-2 ring-inset ring-primary-500' => $day['is_today'],
                     ])>
                    <div class="mb-2 text-sm font-semibold">{{ $day['date']->day }}</div>
                    <div class="space-y-1">
                        @foreach ($day['events'] as $event)
                            <a href="{{ $event['url'] }}"
                               @class([
                                  'block truncate rounded-md px-2 py-1 text-xs font-medium',
                                  'bg-info-50 text-info-700 dark:bg-info-400/10 dark:text-info-300' => $event['type'] === 'follow_up',
                                  'bg-warning-50 text-warning-700 dark:bg-warning-400/10 dark:text-warning-300' => $event['type'] === 'task',
                               ])>
                                {{ $event['title'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
