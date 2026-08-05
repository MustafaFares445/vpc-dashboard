<?php

namespace App\Filament\Resources\ClientInteractions\Pages;

use App\Filament\Resources\ClientInteractions\ClientInteractionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientInteraction extends EditRecord
{
    protected static string $resource = ClientInteractionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
