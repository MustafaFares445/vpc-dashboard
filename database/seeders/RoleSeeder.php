<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }

        Role::findByName(UserRole::Admin->value, 'web')
            ->syncPermissions(array_column(PermissionName::cases(), 'value'));

        Role::findByName(UserRole::Employee->value, 'web')
            ->syncPermissions(PermissionName::employeeDefaults());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
