<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen redirects to login with informative message for Phase 3.1', function () {
    $response = $this->get(route('register'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('nim');
});

test('public registration endpoint is disabled for Phase 3.1', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertForbidden();
    $this->assertGuest();
});
