<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $selectedRole = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedRole = $this->data['role'] ?? $this->record->getRoleNames()->first();

        if (
            $this->record->is(auth()->user()) &&
            array_key_exists('is_active', $data) &&
            ! $data['is_active']
        ) {
            throw ValidationException::withMessages([
                'data.is_active' => 'لا يمكنك تعطيل حسابك الحالي.',
            ]);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var User $record */
        $record = $this->record;
        $record->syncRoles([$this->selectedRole ?? 'employee']);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => $this->record->isNot(auth()->user())),
        ];
    }
}
