<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Enums\ContactMethod;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InteractionsRelationManager extends RelationManager
{
    protected static string $relationship = 'interactions';

    protected static ?string $title = 'سجل التواصل';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contacted_at')
                    ->label('تاريخ التواصل')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('contact_method')
                    ->label('وسيلة التواصل')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof ContactMethod ? $state->label() : (ContactMethod::tryFrom($state)?->label() ?? '—')),
                TextColumn::make('note')
                    ->label('الملاحظة')
                    ->limit(100)
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('الموظف')
                    ->placeholder('—'),
                TextColumn::make('next_follow_up_at')
                    ->label('المتابعة القادمة')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('contacted_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
