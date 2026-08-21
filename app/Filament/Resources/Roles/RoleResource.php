<?php

namespace App\Filament\Resources\Roles;

use App\Enums\PermissionName;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'النظام';
    }

    public static function getNavigationLabel(): string
    {
        return 'الأدوار والصلاحيات';
    }

    public static function getModelLabel(): string
    {
        return 'دور';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الأدوار والصلاحيات';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الدور')->schema([
                TextInput::make('name')
                    ->label('اسم الدور')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('guard_name')
                    ->default('web')
                    ->hidden()
                    ->dehydrated(true),
                Select::make('permissions')
                    ->label('الصلاحيات')
                    ->relationship('permissions', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Model $record): string => PermissionName::tryFrom($record->name)?->label() ?? $record->name)
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الدور')->searchable()->sortable(),
                TextColumn::make('permissions.name')
                    ->label('الصلاحيات')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PermissionName::tryFrom($state)?->label() ?? $state),
                TextColumn::make('users_count')->label('عدد المستخدمين')->counts('users')->sortable(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->isSuperAdmin() ?? false)
            && ! in_array($record->name, ['admin', 'employee'], true);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
