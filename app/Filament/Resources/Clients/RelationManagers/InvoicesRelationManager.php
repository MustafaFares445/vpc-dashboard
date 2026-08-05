<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'الفواتير المرتبطة';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->url(fn (Invoice $record): string => InvoiceResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('issue_date')
                    ->label('تاريخ الإصدار')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money((string) config('app.currency'))
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->money((string) config('app.currency')),
                TextColumn::make('status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof InvoiceStatus ? $state->label() : (InvoiceStatus::tryFrom($state)?->label() ?? $state)),
            ])
            ->defaultSort('issue_date', 'desc');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
