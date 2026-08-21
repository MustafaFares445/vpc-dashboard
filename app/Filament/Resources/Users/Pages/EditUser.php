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
        $this->selectedRole = $this->data['role'] ?? $this->record->getRoleNames()->first() ?? 'employee';
        $deactivating = array_key_exists('is_active', $data) && ! $data['is_active'];
        $removingSuperAdmin = $this->record->isSuperAdmin()
            && array_key_exists('is_super_admin', $data)
            && ! $data['is_super_admin'];

        if ($this->record->is(auth()->user()) && ($deactivating || $removingSuperAdmin)) {
            throw ValidationException::withMessages([
                'data.is_super_admin' => 'لا يمكنك تعطيل حسابك الحالي أو إزالة صلاحية Super Admin منه.',
            ]);
        }

        if (
            $this->record->isSuperAdmin()
            && ($deactivating || $removingSuperAdmin)
            && User::query()->where('is_super_admin', true)->where('is_active', true)->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'data.is_super_admin' => 'يجب أن يبقى Super Admin نشط واحد على الأقل في النظام.',
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
