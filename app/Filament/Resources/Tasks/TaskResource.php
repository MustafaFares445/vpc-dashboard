<?php

namespace App\Filament\Resources\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Filament\Resources\Tasks\Pages\EditTask;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'المهام';
    }

    public static function getNavigationLabel(): string
    {
        return 'المهام';
    }

    public static function getModelLabel(): string
    {
        return 'مهمة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المهام';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات المهمة')
                ->columns(2)
                ->schema([
                    TextInput::make('title')->label('العنوان')->required()->maxLength(255)->disabled(fn (): bool => ! auth()->user()->can('tasks.manage'))->columnSpanFull(),
                    Textarea::make('description')->label('الوصف')->rows(4)->disabled(fn (): bool => ! auth()->user()->can('tasks.manage'))->columnSpanFull(),
                    Select::make('assigned_to')
                        ->label('الموظف المسؤول')
                        ->options(fn (): array => User::query()->employees()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                        ->default(fn (): ?int => auth()->user()->isEmployee() ? auth()->id() : null)
                        ->searchable()->preload()->required()
                        ->disabled(fn (): bool => ! auth()->user()->can('tasks.manage'))
                        ->dehydrated(),
                    Select::make('client_id')
                        ->label('العميل')
                        ->options(fn (): array => Client::query()->visibleTo(auth()->user())->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()->preload()->disabled(fn (): bool => ! auth()->user()->can('tasks.manage')),
                    DateTimePicker::make('due_at')->label('الموعد النهائي')->seconds(false)->disabled(fn (): bool => ! auth()->user()->can('tasks.manage')),
                    Select::make('priority')->label('الأولوية')->options(TaskPriority::options())->default(TaskPriority::Medium->value)->required()->disabled(fn (): bool => ! auth()->user()->can('tasks.manage')),
                    Select::make('status')->label('الحالة')->options(TaskStatus::options())->default(TaskStatus::Pending->value)->required(),
                    Textarea::make('notes')->label('ملاحظات التنفيذ')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('المهمة')->searchable()->sortable(),
                TextColumn::make('assignedUser.name')->label('الموظف')->sortable(),
                TextColumn::make('client.name')->label('العميل')->placeholder('—')->searchable(),
                TextColumn::make('priority')->label('الأولوية')->badge()->formatStateUsing(fn ($state): string => $state instanceof TaskPriority ? $state->label() : (TaskPriority::tryFrom($state)?->label() ?? $state)),
                TextColumn::make('status')->label('الحالة')->badge()->formatStateUsing(fn ($state): string => $state instanceof TaskStatus ? $state->label() : (TaskStatus::tryFrom($state)?->label() ?? $state)),
                TextColumn::make('due_at')->label('الموعد النهائي')->dateTime()->placeholder('—')->sortable()->color(fn (Task $record): ?string => $record->is_overdue ? 'danger' : null),
                IconColumn::make('is_overdue')->label('متأخرة')->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(TaskStatus::options()),
                SelectFilter::make('priority')->label('الأولوية')->options(TaskPriority::options()),
                SelectFilter::make('assigned_to')
                    ->label('الموظف')
                    ->relationship('assignedUser', 'name', modifyQueryUsing: fn (Builder $query): Builder => $query->employees())
                    ->visible(fn (): bool => auth()->user()->can('tasks.manage')),
                Filter::make('overdue')->label('المهام المتأخرة')->query(fn (Builder $query): Builder => $query->overdue()),
            ])
            ->recordActions([
                Action::make('complete')->label('إكمال')->icon(Heroicon::OutlinedCheckCircle)->color('success')->requiresConfirmation()
                    ->visible(fn (Task $record): bool => $record->status !== TaskStatus::Completed)
                    ->action(fn (Task $record) => $record->update(['status' => TaskStatus::Completed])),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('due_at');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user())->with(['assignedUser', 'client']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }
}
