<x-filament-panels::page>
    @php($summary = $this->summary())

    <div class="space-y-6" dir="rtl">
        <x-filament::section heading="الفترة الزمنية">
            <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-1">
                    <span class="text-sm font-medium">من</span>
                    <input type="date" wire:model.live.debounce.500ms="from" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-900" />
                </label>
                <label class="space-y-1">
                    <span class="text-sm font-medium">إلى</span>
                    <input type="date" wire:model.live.debounce.500ms="to" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-gray-900" />
                </label>
            </div>
        </x-filament::section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                'الإيرادات' => number_format($summary['income'], 2),
                'المصاريف' => number_format($summary['expenses'], 2),
                'التكاليف' => number_format($summary['costs'], 2),
                'الربح' => number_format($summary['profit'], 2),
                'صافي الربح' => number_format($summary['net_profit'], 2).' ('.$summary['net_profit_percentage'].'%)',
                'عملاء جدد' => $summary['new_clients'],
                'مهام مكتملة' => $summary['completed_tasks'],
                'مهام متأخرة' => $summary['overdue_tasks'],
                'متابعات منفذة' => $summary['completed_follow_ups'],
            ] as $label => $value)
                <x-filament::section compact>
                    <div class="text-sm text-gray-500">{{ $label }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $value }}</div>
                </x-filament::section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
