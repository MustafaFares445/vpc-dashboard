<x-filament-panels::page>
    @php
        $summary = $this->summary();

        $financialCards = [
            [
                'label' => 'الإيرادات',
                'value' => number_format($summary['income'], 2),
                'icon' => 'heroicon-o-arrow-trending-up',
                'tone' => 'success',
            ],
            [
                'label' => 'المصاريف',
                'value' => number_format($summary['expenses'], 2),
                'icon' => 'heroicon-o-arrow-trending-down',
                'tone' => 'danger',
            ],
            [
                'label' => 'التكاليف',
                'value' => number_format($summary['costs'], 2),
                'icon' => 'heroicon-o-receipt-percent',
                'tone' => 'warning',
            ],
            [
                'label' => 'الربح',
                'value' => number_format($summary['profit'], 2),
                'icon' => 'heroicon-o-banknotes',
                'tone' => 'primary',
            ],
            [
                'label' => 'صافي الربح',
                'value' => number_format($summary['net_profit'], 2),
                'percentage' => number_format((float) $summary['net_profit_percentage'], 1).'%',
                'icon' => 'heroicon-o-scale',
                'tone' => $summary['net_profit'] >= 0 ? 'success' : 'danger',
            ],
        ];

        $activityCards = [
            [
                'label' => 'عملاء جدد',
                'value' => $summary['new_clients'],
                'icon' => 'heroicon-o-user-plus',
                'tone' => 'primary',
            ],
            [
                'label' => 'مهام مكتملة',
                'value' => $summary['completed_tasks'],
                'icon' => 'heroicon-o-check-circle',
                'tone' => 'success',
            ],
            [
                'label' => 'مهام متأخرة',
                'value' => $summary['overdue_tasks'],
                'icon' => 'heroicon-o-clock',
                'tone' => 'danger',
            ],
            [
                'label' => 'متابعات منفذة',
                'value' => $summary['completed_follow_ups'],
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'tone' => 'warning',
            ],
        ];
    @endphp

    <div class="compact-report" dir="rtl">
        <section class="compact-report__filter">
            <div class="compact-report__filter-heading">
                <span class="compact-report__filter-icon">
                    <x-filament::icon icon="heroicon-o-calendar-days" />
                </span>

                <div>
                    <h2>الفترة الزمنية</h2>
                    <p>اختر الفترة التي تريد عرض ملخص التقرير خلالها.</p>
                </div>
            </div>

            <div class="compact-report__date-grid">
                <label class="compact-report__field">
                    <span>من</span>
                    <input
                        type="date"
                        wire:model.live.debounce.500ms="from"
                        class="compact-report__date-input"
                    />
                </label>

                <label class="compact-report__field">
                    <span>إلى</span>
                    <input
                        type="date"
                        wire:model.live.debounce.500ms="to"
                        class="compact-report__date-input"
                    />
                </label>
            </div>
        </section>

        <section class="compact-report__section">
            <div class="compact-report__section-heading">
                <div>
                    <h2>الملخص المالي</h2>
                    <p>نظرة سريعة على الأداء المالي خلال الفترة المحددة.</p>
                </div>
            </div>

            <div class="compact-report__cards compact-report__cards--financial">
                @foreach ($financialCards as $card)
                    <article class="compact-report__card" data-tone="{{ $card['tone'] }}">
                        <div class="compact-report__card-top">
                            <span class="compact-report__card-icon">
                                <x-filament::icon :icon="$card['icon']" />
                            </span>

                            @isset($card['percentage'])
                                <span class="compact-report__percentage" dir="ltr">
                                    {{ $card['percentage'] }}
                                </span>
                            @endisset
                        </div>

                        <div class="compact-report__card-content">
                            <span class="compact-report__label">{{ $card['label'] }}</span>
                            <strong class="compact-report__value" dir="ltr">{{ $card['value'] }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="compact-report__section">
            <div class="compact-report__section-heading">
                <div>
                    <h2>النشاط والمتابعة</h2>
                    <p>مؤشرات العملاء والمهام والمتابعات ضمن نفس الفترة.</p>
                </div>
            </div>

            <div class="compact-report__cards compact-report__cards--activity">
                @foreach ($activityCards as $card)
                    <article class="compact-report__card" data-tone="{{ $card['tone'] }}">
                        <div class="compact-report__card-top">
                            <span class="compact-report__card-icon">
                                <x-filament::icon :icon="$card['icon']" />
                            </span>
                        </div>

                        <div class="compact-report__card-content">
                            <span class="compact-report__label">{{ $card['label'] }}</span>
                            <strong class="compact-report__value" dir="ltr">{{ $card['value'] }}</strong>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>

    <style>
        .compact-report {
            display: grid;
            gap: 1.5rem;
        }

        .compact-report__filter,
        .compact-report__card {
            border: 1px solid rgb(229 231 235);
            background: rgb(255 255 255);
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
        }

        .compact-report__filter {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 0.9fr);
            align-items: center;
            gap: 1.5rem;
            padding: 1.25rem;
            border-radius: 1rem;
        }

        .compact-report__filter-heading,
        .compact-report__section-heading,
        .compact-report__card-top {
            display: flex;
            align-items: center;
        }

        .compact-report__filter-heading {
            gap: 0.875rem;
        }

        .compact-report__filter-icon,
        .compact-report__card-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .compact-report__filter-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.875rem;
            background: rgb(245 158 11 / 0.12);
            color: rgb(217 119 6);
        }

        .compact-report__filter-icon svg {
            width: 1.35rem;
            height: 1.35rem;
        }

        .compact-report__filter-heading h2,
        .compact-report__section-heading h2 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: rgb(17 24 39);
        }

        .compact-report__filter-heading p,
        .compact-report__section-heading p {
            margin: 0.25rem 0 0;
            font-size: 0.8rem;
            color: rgb(107 114 128);
        }

        .compact-report__date-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.875rem;
        }

        .compact-report__field {
            display: grid;
            gap: 0.4rem;
        }

        .compact-report__field > span {
            font-size: 0.78rem;
            font-weight: 600;
            color: rgb(75 85 99);
        }

        .compact-report__date-input {
            width: 100%;
            min-height: 2.55rem;
            border: 1px solid rgb(209 213 219);
            border-radius: 0.7rem;
            background: rgb(255 255 255);
            padding: 0.55rem 0.75rem;
            color: rgb(17 24 39);
            outline: none;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .compact-report__date-input:focus {
            border-color: rgb(245 158 11);
            box-shadow: 0 0 0 3px rgb(245 158 11 / 0.12);
        }

        .compact-report__section {
            display: grid;
            gap: 0.875rem;
        }

        .compact-report__section-heading {
            justify-content: space-between;
            padding-inline: 0.25rem;
        }

        .compact-report__cards {
            display: grid;
            gap: 1rem;
        }

        .compact-report__cards--financial {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .compact-report__cards--activity {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .compact-report__card {
            position: relative;
            min-width: 0;
            min-height: 9.25rem;
            overflow: hidden;
            border-radius: 1rem;
            padding: 1rem;
            transition: transform 150ms ease, box-shadow 150ms ease, border-color 150ms ease;
        }

        .compact-report__card::before {
            content: '';
            position: absolute;
            inset-block: 0;
            inset-inline-start: 0;
            width: 3px;
            background: var(--report-accent);
        }

        .compact-report__card:hover {
            transform: translateY(-2px);
            border-color: color-mix(in srgb, var(--report-accent) 35%, rgb(229 231 235));
            box-shadow: 0 8px 24px rgb(0 0 0 / 0.07);
        }

        .compact-report__card[data-tone='primary'] {
            --report-accent: rgb(59 130 246);
            --report-accent-soft: rgb(59 130 246 / 0.1);
        }

        .compact-report__card[data-tone='success'] {
            --report-accent: rgb(16 185 129);
            --report-accent-soft: rgb(16 185 129 / 0.1);
        }

        .compact-report__card[data-tone='warning'] {
            --report-accent: rgb(245 158 11);
            --report-accent-soft: rgb(245 158 11 / 0.1);
        }

        .compact-report__card[data-tone='danger'] {
            --report-accent: rgb(239 68 68);
            --report-accent-soft: rgb(239 68 68 / 0.1);
        }

        .compact-report__card-top {
            justify-content: space-between;
            gap: 0.75rem;
        }

        .compact-report__card-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            background: var(--report-accent-soft);
            color: var(--report-accent);
        }

        .compact-report__card-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .compact-report__percentage {
            display: inline-flex;
            align-items: center;
            min-height: 1.75rem;
            border-radius: 9999px;
            background: var(--report-accent-soft);
            padding: 0.25rem 0.55rem;
            color: var(--report-accent);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .compact-report__card-content {
            display: grid;
            gap: 0.35rem;
            margin-top: 1.25rem;
        }

        .compact-report__label {
            font-size: 0.82rem;
            font-weight: 600;
            color: rgb(107 114 128);
        }

        .compact-report__value {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: clamp(1.35rem, 2vw, 1.85rem);
            line-height: 1.2;
            font-variant-numeric: tabular-nums;
            color: rgb(17 24 39);
        }

        .dark .compact-report__filter,
        .dark .compact-report__card {
            border-color: rgb(255 255 255 / 0.1);
            background: rgb(24 24 27);
            box-shadow: none;
        }

        .dark .compact-report__filter-heading h2,
        .dark .compact-report__section-heading h2,
        .dark .compact-report__value {
            color: rgb(250 250 250);
        }

        .dark .compact-report__filter-heading p,
        .dark .compact-report__section-heading p,
        .dark .compact-report__label {
            color: rgb(161 161 170);
        }

        .dark .compact-report__field > span {
            color: rgb(212 212 216);
        }

        .dark .compact-report__date-input {
            border-color: rgb(255 255 255 / 0.1);
            background: rgb(9 9 11);
            color: rgb(250 250 250);
            color-scheme: dark;
        }

        @media (max-width: 1280px) {
            .compact-report__cards--financial {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .compact-report__cards--activity {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .compact-report__filter {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .compact-report__date-grid,
            .compact-report__cards--financial,
            .compact-report__cards--activity {
                grid-template-columns: 1fr;
            }

            .compact-report__filter {
                padding: 1rem;
            }

            .compact-report__card {
                min-height: 8.5rem;
            }
        }
    </style>
</x-filament-panels::page>
