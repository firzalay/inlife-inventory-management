<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login page is accessible', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('register page is accessible', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('user can register with valid credentials', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('register.pending'));
    $this->assertGuest();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'status' => 'pending',
    ]);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('user cannot login with invalid password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('correct-password'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});

test('user cannot login with non-existent email', function () {
    $response = $this->post('/login', [
        'email' => 'nobody@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('forgot password page is accessible', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('unauthenticated user is redirected to login from dashboard', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});
