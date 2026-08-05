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

it('allows only admins to manage users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $employee = User::factory()->create();
    $employee->assignRole('employee');

    expect($admin->can('viewAny', User::class))->toBeTrue()
        ->and($employee->can('viewAny', User::class))->toBeFalse();
});
