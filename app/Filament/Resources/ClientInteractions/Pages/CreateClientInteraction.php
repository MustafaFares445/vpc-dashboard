<?php

namespace App\Filament\Resources\ClientInteractions\Pages;

use App\Filament\Resources\ClientInteractions\ClientInteractionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientInteraction extends CreateRecord
{
    protected static string $resource = ClientInteractionResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array { $data['user_id'] = auth()->id(); return $data; }
}
