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
        $deactivating = array_key_exists('is_active', $data) && ! $data['is_active'];
        $removingAdminRole = $this->record->isAdmin() && $this->selectedRole !== 'admin';

        if ($this->record->is(auth()->user()) && ($deactivating || $removingAdminRole)) {
            throw ValidationException::withMessages([
                'data.is_active' => 'لا يمكنك تعطيل حسابك الحالي أو إزالة دور المدير منه.',
            ]);
        }

        if (
            $this->record->isAdmin() &&
            ($deactivating || $removingAdminRole) &&
            User::role('admin')->where('is_active', true)->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'data.is_active' => 'يجب أن يبقى مدير نشط واحد على الأقل في النظام.',
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
                ->visible(fn (): bool => auth()->user()->can('delete', $this->record)),
        ];
    }
}
