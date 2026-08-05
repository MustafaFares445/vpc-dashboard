<?php

namespace App\Filament\Resources\JournalEntries;

use App\Filament\Resources\JournalEntries\Pages\CreateJournalEntry;
use App\Filament\Resources\JournalEntries\Pages\ListJournalEntries;
use App\Models\JournalEntry;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'المحاسبة';
    }

    public static function getNavigationLabel(): string
    {
        return 'القيود اليومية';
    }

    public static function getModelLabel(): string
    {
        return 'قيد يومي';
    }

    public static function getPluralModelLabel(): string
    {
        return 'القيود اليومية';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات القيد')
                ->columns(2)
                ->schema([
                    DatePicker::make('entry_date')->label('تاريخ القيد')->default(today())->required(),
                    TextInput::make('reference')->label('المرجع')->maxLength(255),
                    Textarea::make('description')->label('الوصف')->rows(3)->columnSpanFull(),
                    Repeater::make('lines')
                        ->label('أسطر القيد')
                        ->minItems(2)
                        ->defaultItems(2)
                        ->columns(4)
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('account_name')->label('الحساب')->required()->columnSpan(2),
                            TextInput::make('debit')->label('مدين')->numeric()->minValue(0)->step(0.01)->default(0),
                            TextInput::make('credit')->label('دائن')->numeric()->minValue(0)->step(0.01)->default(0),
                            Textarea::make('notes')->label('ملاحظة')->rows(2)->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $currency = config('app.currency', 'USD');

        return $table
            ->columns([
                TextColumn::make('entry_date')->label('التاريخ')->date()->sortable(),
                TextColumn::make('reference')->label('المرجع')->searchable()->placeholder('—'),
                TextColumn::make('description')->label('الوصف')->limit(60),
                TextColumn::make('lines_count')->label('عدد الأسطر'),
                TextColumn::make('lines_sum_debit')->label('إجمالي المدين')->money($currency),
                TextColumn::make('lines_sum_credit')->label('إجمالي الدائن')->money($currency),
                TextColumn::make('creator.name')->label('أنشأه')->placeholder('النظام'),
            ])
            ->defaultSort('entry_date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('creator')
            ->withCount('lines')
            ->withSum('lines', 'debit')
            ->withSum('lines', 'credit');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournalEntries::route('/'),
            'create' => CreateJournalEntry::route('/create'),
        ];
    }
}
