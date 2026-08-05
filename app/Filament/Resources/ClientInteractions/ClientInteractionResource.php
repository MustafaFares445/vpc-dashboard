<?php

namespace App\Filament\Resources\ClientInteractions;

use App\Enums\ContactMethod;
use App\Filament\Resources\ClientInteractions\Pages\CreateClientInteraction;
use App\Filament\Resources\ClientInteractions\Pages\EditClientInteraction;
use App\Filament\Resources\ClientInteractions\Pages\ListClientInteractions;
use App\Models\Client;
use App\Models\ClientInteraction;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ClientInteractionResource extends Resource
{
    protected static ?string $model = ClientInteraction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'العملاء';
    }

    public static function getNavigationLabel(): string
    {
        return 'المتابعات';
    }

    public static function getModelLabel(): string
    {
        return 'متابعة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المتابعات';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تسجيل متابعة')->columns(2)->schema([
                Select::make('client_id')->label('العميل')
                    ->options(fn (): array => Client::query()->visibleTo(auth()->user())->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->preload()->required(),
                DateTimePicker::make('contacted_at')->label('تاريخ التواصل')->default(now())->seconds(false)->required(),
                Select::make('contact_method')->label('وسيلة التواصل')->options(ContactMethod::options()),
                DateTimePicker::make('next_follow_up_at')->label('المتابعة القادمة')->seconds(false),
                Textarea::make('note')->label('الملاحظة')->required()->rows(5)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('client.name')->label('العميل')->searchable()->sortable(),
            TextColumn::make('user.name')->label('الموظف')->placeholder('—'),
            TextColumn::make('contact_method')->label('الوسيلة')->badge()
                ->formatStateUsing(fn ($state): string => $state instanceof ContactMethod ? $state->label() : (ContactMethod::tryFrom($state)?->label() ?? '—')),
            TextColumn::make('contacted_at')->label('تاريخ التواصل')->dateTime()->sortable(),
            TextColumn::make('next_follow_up_at')->label('المتابعة القادمة')->dateTime()->placeholder('—')->sortable(),
            TextColumn::make('note')->label('الملاحظة')->limit(60)->wrap(),
        ])->filters([
            SelectFilter::make('contact_method')->label('الوسيلة')->options(ContactMethod::options()),
        ])->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('contacted_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['client', 'user']);
        if (auth()->user()->isAdmin()) {
            return $query;
        }

        return $query->whereHas('client', fn (Builder $clientQuery): Builder => $clientQuery->where('assigned_to', auth()->id()));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientInteractions::route('/'),
            'create' => CreateClientInteraction::route('/create'),
            'edit' => EditClientInteraction::route('/{record}/edit'),
        ];
    }
}
