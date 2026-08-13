<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('redirects guests from the admin panel to login', function () {
    $this->get('/admin')->assertRedirect();
});

it('redirects an active administrator from the admin root to the calendar', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/admin')
        ->assertRedirect('/admin/calendar');
});
