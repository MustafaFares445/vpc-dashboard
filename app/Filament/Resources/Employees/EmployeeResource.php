<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Models\Employee;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'النظام';
    }

    public static function getNavigationLabel(): string
    {
        return 'الموظفون';
    }

    public static function getModelLabel(): string
    {
        return 'موظف';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الموظفون';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الموظف')
                    ->description('سجل داخلي للموظف فقط، ولا يمنحه حساباً أو صلاحية للدخول إلى لوحة التحكم.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('job_title')
                            ->label('المسمى الوظيفي')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('hire_date')
                            ->label('تاريخ التعيين'),
                        Toggle::make('is_active')
                            ->label('موظف نشط')
                            ->default(true)
                            ->required(),
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
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('job_title')
                    ->label('المسمى الوظيفي')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('hire_date')
                    ->label('تاريخ التعيين')
                    ->date()
                    ->placeholder('—')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('حالة الموظف'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'job_title', 'phone', 'email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
