<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
});

/**
 * Helper to create a user and assign a role.
 */
function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ================================================================
// Admin access tests
// ================================================================

test('admin can access the dashboard', function () {
    $admin = userWithRole('Admin');

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertStatus(200);
});

test('admin can access admin-only routes', function () {
    $admin = userWithRole('Admin');

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
});

test('admin can access inventory routes', function () {
    $admin = userWithRole('Admin');

    $response = $this->actingAs($admin)->get('/inventory');

    $response->assertStatus(200);
});

test('admin can access reports routes', function () {
    $admin = userWithRole('Admin');

    $response = $this->actingAs($admin)->get('/reports');

    $response->assertStatus(200);
});

// ================================================================
// Staff access tests
// ================================================================

test('staff can access the dashboard', function () {
    $staff = userWithRole('Staff');

    $response = $this->actingAs($staff)->get('/dashboard');

    $response->assertStatus(200);
});

test('staff can access inventory routes', function () {
    $staff = userWithRole('Staff');

    $response = $this->actingAs($staff)->get('/inventory');

    $response->assertStatus(200);
});

test('staff cannot access admin-only routes', function () {
    $staff = userWithRole('Staff');

    $response = $this->actingAs($staff)->get('/admin');

    $response->assertStatus(403);
});

test('staff cannot access reports routes', function () {
    $staff = userWithRole('Staff');

    $response = $this->actingAs($staff)->get('/reports');

    $response->assertStatus(403);
});

// ================================================================
// Manager access tests
// ================================================================

test('manager can access the dashboard', function () {
    $manager = userWithRole('Manager');

    $response = $this->actingAs($manager)->get('/dashboard');

    $response->assertStatus(200);
});

test('manager can access reports routes', function () {
    $manager = userWithRole('Manager');

    $response = $this->actingAs($manager)->get('/reports');

    $response->assertStatus(200);
});

test('manager cannot access admin-only routes', function () {
    $manager = userWithRole('Manager');

    $response = $this->actingAs($manager)->get('/admin');

    $response->assertStatus(403);
});

test('manager cannot access inventory routes', function () {
    $manager = userWithRole('Manager');

    $response = $this->actingAs($manager)->get('/inventory');

    $response->assertStatus(403);
});

// ================================================================
// Unauthenticated access
// ================================================================

test('unauthenticated user cannot access admin routes', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

test('unauthenticated user cannot access inventory routes', function () {
    $response = $this->get('/inventory');

    $response->assertRedirect('/login');
});

test('unauthenticated user cannot access reports routes', function () {
    $response = $this->get('/reports');

    $response->assertRedirect('/login');
});
