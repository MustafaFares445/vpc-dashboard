<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('allows active users and blocks inactive users from the panel', function () {
    $active = User::factory()->create(['is_active' => true]);
    $inactive = User::factory()->create(['is_active' => false]);

    expect($active->canAccessPanel(filament()->getPanel('admin')))->toBeTrue()
        ->and($inactive->canAccessPanel(filament()->getPanel('admin')))->toBeFalse();
});

it('allows only super admins to manage users', function () {
    $superAdmin = User::factory()->create(['is_super_admin' => true]);
    $superAdmin->assignRole('admin');
    $admin = User::factory()->create(['is_super_admin' => false]);
    $admin->assignRole('admin');
    $employee = User::factory()->create(['is_super_admin' => false]);
    $employee->assignRole('employee');

    expect($superAdmin->can('viewAny', User::class))->toBeTrue()
        ->and($admin->can('viewAny', User::class))->toBeFalse()
        ->and($employee->can('viewAny', User::class))->toBeFalse();
});
