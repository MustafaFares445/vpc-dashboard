<?php

use App\Models\User;

it('redirects guests from the admin panel to login', function () {
    $this->get('/admin')->assertRedirect();
});

it('allows an active authenticated user to reach the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});
