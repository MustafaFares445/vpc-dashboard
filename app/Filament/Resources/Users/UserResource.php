<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'النظام';
    }

    public static function getNavigationLabel(): string
    {
        return 'المستخدمون والموظفون';
    }

    public static function getModelLabel(): string
    {
        return 'مستخدم';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المستخدمون والموظفون';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الحساب')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8),
                        Select::make('role')
                            ->label('الدور')
                            ->options(fn (): array => Role::query()
                                ->where('guard_name', 'web')
                                ->orderBy('name')
                                ->pluck('name', 'name')
                                ->all())
                            ->default('employee')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Select $component, ?User $record): void {
                                $component->state($record?->getRoleNames()->first() ?? 'employee');
                            }),
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->required(),
                        Toggle::make('is_super_admin')
                            ->label('Super Admin')
                            ->helperText('يتجاوز جميع الصلاحيات، وهو الوحيد القادر على إدارة المستخدمين والأدوار والصلاحيات.')
                            ->default(false),
                    ]),
                Section::make('بيانات الموظف')
                    ->description('كل مستخدم غير Super Admin يعتبر موظفاً ويمكن إسناد العملاء والمهام والمتابعات إليه.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('job_title')
                            ->label('المسمى الوظيفي')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(50),
                        DatePicker::make('hire_date')
                            ->label('تاريخ التعيين'),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                TextColumn::make('email')->label('البريد الإلكتروني')->searchable(),
                TextColumn::make('job_title')->label('المسمى الوظيفي')->placeholder('—')->searchable(),
                TextColumn::make('roles.name')->label('الدور')->badge(),
                IconColumn::make('is_super_admin')->label('Super Admin')->boolean(),
                IconColumn::make('is_active')->label('نشط')->boolean(),
                TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('الدور')
                    ->relationship('roles', 'name'),
                TernaryFilter::make('is_super_admin')
                    ->label('Super Admin'),
                TernaryFilter::make('is_active')
                    ->label('حالة الحساب'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'job_title', 'phone'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
