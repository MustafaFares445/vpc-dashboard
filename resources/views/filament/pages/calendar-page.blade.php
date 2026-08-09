<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">
        <div class="flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="previousMonth" color="gray" icon="heroicon-m-chevron-right">
                    الشهر السابق
                </x-filament::button>

                <x-filament::button wire:click="currentMonth" color="gray">
                    اليوم
                </x-filament::button>

                <x-filament::button wire:click="nextMonth" color="gray" icon="heroicon-m-chevron-left" icon-position="after">
                    الشهر التالي
                </x-filament::button>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                مواعيد متابعة العملاء والمواعيد النهائية للمهام
            </p>
        </div>

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="grid min-w-[56rem] grid-cols-7">
                @foreach (['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'] as $dayName)
                    <div class="border-b border-l border-gray-200 bg-gray-50 px-3 py-3 text-center text-sm font-semibold text-gray-700 last:border-l-0 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                        {{ $dayName }}
                    </div>
                @endforeach

                @foreach ($this->calendarDays() as $day)
                    <div
                        wire:key="calendar-day-{{ $day['date']->toDateString() }}"
                        @class([
                            'relative min-h-36 border-b border-l border-gray-200 p-2.5 dark:border-white/10',
                            'bg-gray-50/70 text-gray-400 dark:bg-gray-950/40 dark:text-gray-500' => ! $day['is_current_month'],
                            'bg-white text-gray-950 dark:bg-gray-900 dark:text-white' => $day['is_current_month'],
                            'ring-2 ring-inset ring-primary-500' => $day['is_today'],
                        ])
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <span
                                @class([
                                    'inline-flex size-7 items-center justify-center rounded-full text-sm font-semibold',
                                    'bg-primary-500 text-white' => $day['is_today'],
                                ])
                            >
                                {{ $day['date']->day }}
                            </span>
                        </div>

                        <div class="space-y-1.5">
                            @foreach ($day['events'] as $event)
                                <a
                                    href="{{ $event['url'] }}"
                                    title="{{ $event['title'] }}"
                                    @class([
                                        'block truncate rounded-md border px-2 py-1.5 text-xs font-medium transition hover:opacity-80',
                                        'border-info-200 bg-info-50 text-info-700 dark:border-info-400/20 dark:bg-info-400/10 dark:text-info-300' => $event['type'] === 'follow_up',
                                        'border-warning-200 bg-warning-50 text-warning-700 dark:border-warning-400/20 dark:bg-warning-400/10 dark:text-warning-300' => $event['type'] === 'task',
                                    ])
                                >
                                    {{ $event['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
