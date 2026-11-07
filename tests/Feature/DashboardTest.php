<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated and verified users can visit the dashboard', function () {
    $user = User::factory()->verified()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('unverified users are redirected from dashboard to verification status', function () {
    $user = User::factory()->create(['status_verifikasi' => 'menunggu']);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('verification.status'));
});
