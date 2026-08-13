<?php

namespace App\Filament\Resources\FinancialTransactions;

use App\Enums\Currency;
use App\Enums\FinancialTransactionType;
use App\Filament\Resources\FinancialTransactions\Pages\CreateFinancialTransaction;
use App\Filament\Resources\FinancialTransactions\Pages\EditFinancialTransaction;
use App\Filament\Resources\FinancialTransactions\Pages\ListFinancialTransactions;
use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FinancialTransactionResource extends Resource
{
    protected static ?string $model = FinancialTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'المحاسبة';
    }

    public static function getNavigationLabel(): string
    {
        return 'الإيرادات والمصاريف';
    }

    public static function getModelLabel(): string
    {
        return 'عملية مالية';
    }

    public static function getPluralModelLabel(): string
    {
        return 'العمليات المالية';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات العملية')
                ->columns(2)
                ->schema([
                    Select::make('type')->label('النوع')->options(self::transactionTypeOptions())->required(),
                    DatePicker::make('date')->label('التاريخ')->default(today())->required(),
                    TextInput::make('amount')->label('المبلغ')->numeric()->minValue(0.01)->step(0.01)->required(),
                    Select::make('currency')->label('العملة')->options(Currency::options())->default(Currency::USD->value)->required(),
                    Select::make('client_id')->label('العميل')->options(fn (): array => Client::query()->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload(),
                    Select::make('invoice_id')
                        ->label('الفاتورة')
                        ->options(fn (): array => self::invoiceOptions())
                        ->searchable()
                        ->preload()
                        ->helperText('يظهر رقم الفاتورة والعميل والإجمالي والمتبقي والحالة والتاريخ لتسهيل الاختيار.')
                        ->columnSpanFull(),
                    Textarea::make('description')->label('الوصف')->rows(4)->columnSpanFull(),
                    SpatieMediaLibraryFileUpload::make('attachments')
                        ->label('المرفقات')
                        ->collection('attachments')
                        ->multiple()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(10240)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label('التاريخ')->date()->sortable(),
                TextColumn::make('type')->label('النوع')->badge()->formatStateUsing(fn ($state): string => $state instanceof FinancialTransactionType ? $state->label() : FinancialTransactionType::from($state)->label()),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state, FinancialTransaction $record): string => number_format((float) $state, 2).' '.$record->currency->value)
                    ->sortable(),
                TextColumn::make('currency')->label('العملة')->badge()->formatStateUsing(fn ($state): string => $state instanceof Currency ? $state->label() : Currency::from($state)->label()),
                TextColumn::make('client.name')->label('العميل')->placeholder('—')->searchable(),
                TextColumn::make('invoice.invoice_number')->label('الفاتورة')->placeholder('—')->searchable(),
                TextColumn::make('description')->label('الوصف')->limit(50)->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('النوع')->options(self::transactionTypeOptions()),
                SelectFilter::make('currency')->label('العملة')->options(Currency::options()),
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from')->label('من'),
                        DatePicker::make('to')->label('إلى'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->betweenDates($data['from'] ?? null, $data['to'] ?? null)),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialTransactions::route('/'),
            'create' => CreateFinancialTransaction::route('/create'),
            'edit' => EditFinancialTransaction::route('/{record}/edit'),
        ];
    }

    private static function transactionTypeOptions(): array
    {
        return [
            FinancialTransactionType::Income->value => FinancialTransactionType::Income->label(),
            FinancialTransactionType::Expense->value => FinancialTransactionType::Expense->label(),
        ];
    }

    private static function invoiceOptions(): array
    {
        return Invoice::query()
            ->with('client')
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->mapWithKeys(fn (Invoice $invoice): array => [$invoice->id => self::invoiceOptionLabel($invoice)])
            ->all();
    }

    private static function invoiceOptionLabel(Invoice $invoice): string
    {
        $currency = config('app.currency', 'USD');
        $client = $invoice->client?->name ?? 'بدون عميل';
        $total = number_format((float) $invoice->total, 2);
        $remaining = number_format(max((float) $invoice->total - (float) $invoice->paid_amount, 0), 2);
        $status = $invoice->status?->label() ?? '—';
        $issueDate = $invoice->issue_date?->format('Y-m-d') ?? '—';

        return "{$invoice->invoice_number} — {$client} — الإجمالي: {$total} {$currency} — المتبقي: {$remaining} {$currency} — {$status} — {$issueDate}";
    }
}
