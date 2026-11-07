<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen with nim', function () {
    $user = User::factory()->verified()->create();

    $response = $this->post(route('login.store'), [
        'nim' => $user->nim,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('unverified users are redirected to verification status on login', function () {
    $user = User::factory()->create(['status_verifikasi' => 'menunggu']);

    $response = $this->post(route('login.store'), [
        'nim' => $user->nim,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.status'));
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->verified()->withTwoFactor()->create();

    $response = $this->post(route('login'), [
        'nim' => $user->nim,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'nim' => $user->nim,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});

test('users are rate limited', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), [
            'nim' => $user->nim,
            'password' => 'wrong-password',
        ]);
    }

    $response = $this->post(route('login.store'), [
        'nim' => $user->nim,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});
