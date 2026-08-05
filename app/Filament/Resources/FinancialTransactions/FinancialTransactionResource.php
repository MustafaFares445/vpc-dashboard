<?php

namespace App\Filament\Resources\FinancialTransactions;

use App\Enums\FinancialTransactionType;
use App\Enums\PaymentStatus;
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
use Filament\Tables\Columns\Summarizers\Sum;
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
                    Select::make('type')->label('النوع')->options(FinancialTransactionType::options())->required(),
                    DatePicker::make('date')->label('التاريخ')->default(today())->required(),
                    TextInput::make('amount')->label('المبلغ')->numeric()->minValue(0.01)->step(0.01)->required(),
                    Select::make('payment_status')->label('حالة الدفع')->options(PaymentStatus::options())->default(PaymentStatus::Paid->value)->required(),
                    Select::make('client_id')->label('العميل')->options(fn (): array => Client::query()->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload(),
                    Select::make('invoice_id')->label('الفاتورة')->options(fn (): array => Invoice::query()->latest()->limit(100)->pluck('invoice_number', 'id')->all())->searchable(),
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
        $currency = config('app.currency', 'USD');

        return $table
            ->columns([
                TextColumn::make('date')->label('التاريخ')->date()->sortable(),
                TextColumn::make('type')->label('النوع')->badge()->formatStateUsing(fn ($state): string => $state instanceof FinancialTransactionType ? $state->label() : FinancialTransactionType::from($state)->label()),
                TextColumn::make('amount')->label('المبلغ')->money($currency)->sortable()->summarize(Sum::make()->money($currency)),
                TextColumn::make('payment_status')->label('الدفع')->badge()->formatStateUsing(fn ($state): string => $state instanceof PaymentStatus ? $state->label() : PaymentStatus::from($state)->label()),
                TextColumn::make('client.name')->label('العميل')->placeholder('—')->searchable(),
                TextColumn::make('description')->label('الوصف')->limit(50)->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('النوع')->options(FinancialTransactionType::options()),
                SelectFilter::make('payment_status')->label('الدفع')->options(PaymentStatus::options()),
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
}
