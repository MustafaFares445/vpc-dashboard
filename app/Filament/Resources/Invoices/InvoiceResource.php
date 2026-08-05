<?php

namespace App\Filament\Resources\Invoices;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Models\Client;
use App\Models\Invoice;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'المحاسبة';
    }

    public static function getNavigationLabel(): string
    {
        return 'الفواتير';
    }

    public static function getModelLabel(): string
    {
        return 'فاتورة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الفواتير';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الفاتورة')->columns(2)->schema([
                TextInput::make('invoice_number')->label('رقم الفاتورة')->placeholder('يُولّد تلقائيًا')->maxLength(255)->unique(ignoreRecord: true),
                Select::make('client_id')->label('العميل')->options(fn (): array => Client::query()->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload()->required(),
                DatePicker::make('issue_date')->label('تاريخ الإصدار')->default(today())->required(),
                DatePicker::make('due_date')->label('تاريخ الاستحقاق')->afterOrEqual('issue_date'),
                TextInput::make('paid_amount')->label('المبلغ المدفوع')->numeric()->minValue(0)->step(0.01)->default(0)->required(),
                Textarea::make('notes')->label('ملاحظات')->rows(3)->columnSpanFull(),
                Repeater::make('items')->label('بنود الفاتورة')->minItems(1)->defaultItems(1)->columns(4)->columnSpanFull()->schema([
                    TextInput::make('description')->label('الوصف')->required()->columnSpan(2),
                    TextInput::make('quantity')->label('الكمية')->numeric()->minValue(0.01)->step(0.01)->default(1)->required(),
                    TextInput::make('unit_price')->label('سعر الوحدة')->numeric()->minValue(0)->step(0.01)->default(0)->required(),
                ]),
                SpatieMediaLibraryFileUpload::make('attachments')->label('المرفقات')->collection('attachments')->multiple()
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])->maxSize(10240)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('invoice_number')->label('رقم الفاتورة')->searchable()->sortable(),
            TextColumn::make('client.name')->label('العميل')->searchable()->sortable(),
            TextColumn::make('issue_date')->label('الإصدار')->date()->sortable(),
            TextColumn::make('due_date')->label('الاستحقاق')->date()->placeholder('—')->sortable(),
            TextColumn::make('total')->label('الإجمالي')->money('USD')->sortable(),
            TextColumn::make('paid_amount')->label('المدفوع')->money('USD')->sortable(),
            TextColumn::make('status')->label('الحالة')->badge()->formatStateUsing(fn ($state): string => $state instanceof InvoiceStatus ? $state->label() : InvoiceStatus::from($state)->label()),
        ])->filters([
            SelectFilter::make('status')->label('الحالة')->options(InvoiceStatus::options()),
            SelectFilter::make('client_id')->label('العميل')->relationship('client', 'name'),
        ])->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('issue_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }
}
