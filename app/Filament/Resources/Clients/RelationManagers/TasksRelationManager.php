<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'المهام المرتبطة';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => auth()->user()->isAdmin()
                ? $query
                : $query->where('assigned_to', auth()->id()))
            ->columns([
                TextColumn::make('title')
                    ->label('المهمة')
                    ->searchable()
                    ->url(fn (Task $record): string => TaskResource::getUrl('edit', ['record' => $record])),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof TaskStatus ? $state->label() : (TaskStatus::tryFrom($state)?->label() ?? $state)),
                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof TaskPriority ? $state->label() : (TaskPriority::tryFrom($state)?->label() ?? $state)),
                TextColumn::make('assignedUser.name')
                    ->label('المسؤول')
                    ->placeholder('—'),
                TextColumn::make('due_at')
                    ->label('الموعد النهائي')
                    ->dateTime()
                    ->placeholder('—')
                    ->color(fn (Task $record): ?string => $record->is_overdue ? 'danger' : null)
                    ->sortable(),
                IconColumn::make('is_overdue')
                    ->label('متأخرة')
                    ->boolean(),
            ])
            ->defaultSort('due_at');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
