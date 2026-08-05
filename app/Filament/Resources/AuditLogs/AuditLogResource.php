<?php

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'النظام';
    }

    public static function getNavigationLabel(): string
    {
        return 'سجل النشاط';
    }

    public static function getModelLabel(): string
    {
        return 'سجل نشاط';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->placeholder('النظام')
                    ->searchable(),
                TextColumn::make('event')
                    ->label('العملية')
                    ->badge(),
                TextColumn::make('auditable_type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),
                TextColumn::make('auditable_id')
                    ->label('المعرف'),
                TextColumn::make('ip_address')
                    ->label('IP'),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('العملية')
                    ->options([
                        'created' => 'إنشاء',
                        'updated' => 'تعديل',
                        'deleted' => 'حذف',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
        ];
    }
}
