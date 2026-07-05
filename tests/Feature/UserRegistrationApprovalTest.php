<?php

use App\Models\User;
use App\Notifications\NewUserRegisteredNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
});

test('newly registered user has status pending and role Staff by default', function () {
    Notification::fake();

    $response = $this->post(route('register'), [
        'name' => 'Budi Pekerti',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('register.pending'));

    $user = User::where('email', 'budi@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->status)->toBe('pending')
        ->and($user->hasRole('Staff'))->toBeTrue();

    // Do NOT auto login
    $this->assertGuest();
});

test('admins receive notification when a new user registers', function () {
    Notification::fake();

    // Create an Admin user
    $admin = User::factory()->create(['status' => 'approved']);
    $admin->assignRole('Admin');

    // Register a new user
    $this->post(route('register'), [
        'name' => 'Budi Pekerti',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'budi@example.com')->first();

    Notification::assertSentTo(
        [$admin],
        NewUserRegisteredNotification::class,
        function ($notification) use ($user) {
            return $notification->registeredUser->id === $user->id;
        }
    );
});

test('pending user is blocked from logging in (web & api)', function () {
    $user = User::factory()->create([
        'status' => 'pending',
        'password' => bcrypt('password'),
    ]);

    // Web Login
    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();

    // API Login
    $apiResponse = $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $apiResponse->assertStatus(403);
    $apiResponse->assertJsonFragment([
        'success' => false,
        'message' => 'Akun Anda belum disetujui oleh Admin. Silakan tunggu konfirmasi.',
    ]);
});

test('rejected user is blocked from logging in with a different message (web & api)', function () {
    $user = User::factory()->create([
        'status' => 'rejected',
        'password' => bcrypt('password'),
    ]);

    // Web Login
    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();

    // API Login
    $apiResponse = $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $apiResponse->assertStatus(403);
    $apiResponse->assertJsonFragment([
        'success' => false,
        'message' => 'Pendaftaran Anda ditolak. Hubungi Admin untuk informasi lebih lanjut.',
    ]);
});

test('admin can approve user and the user can then login normally', function () {
    $admin = User::factory()->create(['status' => 'approved']);
    $admin->assignRole('Admin');

    $user = User::factory()->create([
        'status' => 'pending',
        'password' => bcrypt('password'),
    ]);

    $response = $this->actingAs($admin)->post(route('users.approve', $user));
    $response->assertStatus(302);

    $user->refresh();
    expect($user->status)->toBe('approved')
        ->and($user->approved_by)->toBe($admin->id)
        ->and($user->approved_at)->not->toBeNull();

    // Try logging in now
    $this->post(route('logout')); // Ensure guest state
    $loginResponse = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('admin can reject user registration', function () {
    $admin = User::factory()->create(['status' => 'approved']);
    $admin->assignRole('Admin');

    $user = User::factory()->create([
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->post(route('users.reject', $user));
    $response->assertStatus(302);

    $user->refresh();
    expect($user->status)->toBe('rejected');
});

test('admin can update user role', function () {
    $admin = User::factory()->create(['status' => 'approved']);
    $admin->assignRole('Admin');

    $user = User::factory()->create(['status' => 'approved']);
    $user->assignRole('Staff');

    $response = $this->actingAs($admin)->patch(route('users.role.update', $user), [
        'role' => 'Manager',
    ]);

    $response->assertStatus(302);
    expect($user->fresh()->hasRole('Manager'))->toBeTrue();
});

test('admin can delete user', function () {
    $admin = User::factory()->create(['status' => 'approved']);
    $admin->assignRole('Admin');

    $user = User::factory()->create();

    $response = $this->actingAs($admin)->delete(route('users.destroy', $user));
    $response->assertRedirect(route('users.index'));

    expect(User::find($user->id))->toBeNull();
});

test('non-admin users cannot access user management endpoints', function () {
    $staff = User::factory()->create(['status' => 'approved']);
    $staff->assignRole('Staff');

    $manager = User::factory()->create(['status' => 'approved']);
    $manager->assignRole('Manager');

    $user = User::factory()->create();

    // Check Staff
    $this->actingAs($staff)->get(route('users.index'))->assertStatus(403);
    $this->actingAs($staff)->get(route('users.show', $user))->assertStatus(403);
    $this->actingAs($staff)->post(route('users.approve', $user))->assertStatus(403);
    $this->actingAs($staff)->post(route('users.reject', $user))->assertStatus(403);
    $this->actingAs($staff)->patch(route('users.role.update', $user))->assertStatus(403);
    $this->actingAs($staff)->delete(route('users.destroy', $user))->assertStatus(403);

    // Check Manager
    $this->actingAs($manager)->get(route('users.index'))->assertStatus(403);
    $this->actingAs($manager)->get(route('users.show', $user))->assertStatus(403);
    $this->actingAs($manager)->post(route('users.approve', $user))->assertStatus(403);
    $this->actingAs($manager)->post(route('users.reject', $user))->assertStatus(403);
    $this->actingAs($manager)->patch(route('users.role.update', $user))->assertStatus(403);
    $this->actingAs($manager)->delete(route('users.destroy', $user))->assertStatus(403);
});
