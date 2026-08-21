<?php

namespace App\Filament\Resources\Clients;

use App\Enums\ClientStatus;
use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Resources\Clients\RelationManagers\InteractionsRelationManager;
use App\Filament\Resources\Clients\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\Clients\RelationManagers\TasksRelationManager;
use App\Models\Client;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function getNavigationGroup(): string|UnitEnum|null { return 'العملاء'; }
    public static function getNavigationLabel(): string { return 'العملاء'; }
    public static function getModelLabel(): string { return 'عميل'; }
    public static function getPluralModelLabel(): string { return 'العملاء'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات العميل')->columns(2)->schema([
                TextInput::make('name')->label('الاسم')->required()->maxLength(255),
                TextInput::make('company_name')->label('الشركة')->maxLength(255),
                TextInput::make('email')->label('البريد الإلكتروني')->email()->maxLength(255),
                TextInput::make('phone')->label('رقم الهاتف')->tel()->maxLength(50),
                Select::make('status')->label('الحالة')->options(ClientStatus::options())->default(ClientStatus::Lead->value)->required(),
                Select::make('assigned_to')->label('الموظف المسؤول')
                    ->options(fn (): array => User::query()->employees()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->preload()->visible(fn (): bool => auth()->user()->can('clients.manage')),
                DateTimePicker::make('next_follow_up_at')->label('المتابعة القادمة')->seconds(false),
                Textarea::make('notes')->label('ملاحظات')->rows(4)->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('attachments')->label('المرفقات')->collection('attachments')->multiple()
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])->maxSize(10240)->columnSpanFull(),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('ملخص العميل')->columns(3)->schema([
                TextEntry::make('name')->label('الاسم'),
                TextEntry::make('company_name')->label('الشركة')->placeholder('—'),
                TextEntry::make('status')->label('الحالة')->badge()->formatStateUsing(fn ($state): string => $state instanceof ClientStatus ? $state->label() : (ClientStatus::tryFrom($state)?->label() ?? $state)),
                TextEntry::make('assignedUser.name')->label('الموظف المسؤول')->placeholder('غير مسند'),
                TextEntry::make('email')->label('البريد الإلكتروني')->placeholder('—'),
                TextEntry::make('phone')->label('رقم الهاتف')->placeholder('—'),
                TextEntry::make('last_contact_at')->label('آخر تواصل')->dateTime()->placeholder('—'),
                TextEntry::make('next_follow_up_at')->label('المتابعة القادمة')->dateTime()->placeholder('—'),
                TextEntry::make('created_at')->label('تاريخ الإضافة')->dateTime(),
            ]),
            Section::make('ملخص النشاط')->columns(4)->schema([
                TextEntry::make('interactions_count')->label('سجلات التواصل')->state(fn (Client $record): int => $record->interactions()->count())->badge(),
                TextEntry::make('tasks_count')->label('المهام')->state(function (Client $record): int {
                    $query = $record->tasks();
                    if (! auth()->user()->can('tasks.manage')) { $query->where('assigned_to', auth()->id()); }
                    return $query->count();
                })->badge(),
                TextEntry::make('overdue_tasks_count')->label('المهام المتأخرة')->state(function (Client $record): int {
                    $query = $record->tasks()->overdue();
                    if (! auth()->user()->can('tasks.manage')) { $query->where('assigned_to', auth()->id()); }
                    return $query->count();
                })->badge()->color(fn (int $state): string => $state > 0 ? 'danger' : 'success'),
                TextEntry::make('invoices_count')->label('الفواتير')->state(fn (Client $record): int => $record->invoices()->count())->badge()->visible(fn (): bool => auth()->user()->can('accounting.view')),
                TextEntry::make('invoice_total')->label('إجمالي الفواتير')->state(fn (Client $record): float => (float) $record->invoices()->sum('total'))->money((string) config('app.currency'), locale: 'en')->visible(fn (): bool => auth()->user()->can('accounting.view')),
                TextEntry::make('invoice_balance')->label('الرصيد غير المدفوع')->state(fn (Client $record): float => (float) $record->invoices()->sum('total') - (float) $record->invoices()->sum('paid_amount'))->money((string) config('app.currency'), locale: 'en')->visible(fn (): bool => auth()->user()->can('accounting.view')),
            ]),
            Section::make('الملاحظات')->schema([TextEntry::make('notes')->label('')->placeholder('لا توجد ملاحظات')->columnSpanFull()]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
            TextColumn::make('company_name')->label('الشركة')->searchable()->toggleable(),
            TextColumn::make('status')->label('الحالة')->badge()->formatStateUsing(fn ($state): string => $state instanceof ClientStatus ? $state->label() : (ClientStatus::tryFrom($state)?->label() ?? $state)),
            TextColumn::make('assignedUser.name')->label('الموظف')->placeholder('غير مسند')->sortable(),
            TextColumn::make('last_contact_at')->label('آخر تواصل')->dateTime()->placeholder('—')->sortable(),
            TextColumn::make('next_follow_up_at')->label('المتابعة القادمة')->dateTime()->placeholder('—')->sortable()->color(fn (Client $record): ?string => $record->next_follow_up_at?->isPast() ? 'danger' : null),
        ])->filters([
            SelectFilter::make('status')->label('الحالة')->options(ClientStatus::options()),
            SelectFilter::make('assigned_to')->label('الموظف')
                ->relationship('assignedUser', 'name', modifyQueryUsing: fn (Builder $query): Builder => $query->employees())
                ->visible(fn (): bool => auth()->user()->can('clients.manage')),
            Filter::make('follow_up_overdue')->label('متابعة متأخرة')->query(fn (Builder $query): Builder => $query->followUpOverdue()),
        ])->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('next_follow_up_at');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user())->with('assignedUser');
    }

    public static function getRelations(): array
    {
        return ['interactions' => InteractionsRelationManager::class, 'tasks' => TasksRelationManager::class, 'invoices' => InvoicesRelationManager::class];
    }

    public static function getGloballySearchableAttributes(): array { return ['name', 'company_name', 'email', 'phone']; }
    public static function getGlobalSearchResultTitle(Model $record): string { return $record->name; }

    public static function getPages(): array
    {
        return ['index' => ListClients::route('/'), 'create' => CreateClient::route('/create'), 'view' => ViewClient::route('/{record}'), 'edit' => EditClient::route('/{record}/edit')];
    }
}
