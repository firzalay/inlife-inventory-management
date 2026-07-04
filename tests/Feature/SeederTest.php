<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('role seeder creates all 3 roles', function () {
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

    expect(Role::where('name', 'Admin')->exists())->toBeTrue()
        ->and(Role::where('name', 'Staff')->exists())->toBeTrue()
        ->and(Role::where('name', 'Manager')->exists())->toBeTrue();
});

test('user seeder creates 3 test accounts', function () {
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    $this->artisan('db:seed', ['--class' => 'UserSeeder']);

    $this->assertDatabaseHas('users', ['email' => 'admin@inventaris.test']);
    $this->assertDatabaseHas('users', ['email' => 'staff@inventaris.test']);
    $this->assertDatabaseHas('users', ['email' => 'manager@inventaris.test']);
});

test('seeded users have correct roles assigned', function () {
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    $this->artisan('db:seed', ['--class' => 'UserSeeder']);

    $admin = User::where('email', 'admin@inventaris.test')->first();
    $staff = User::where('email', 'staff@inventaris.test')->first();
    $manager = User::where('email', 'manager@inventaris.test')->first();

    expect($admin->hasRole('Admin'))->toBeTrue()
        ->and($staff->hasRole('Staff'))->toBeTrue()
        ->and($manager->hasRole('Manager'))->toBeTrue();
});

test('seeded admin user can login', function () {
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    $this->artisan('db:seed', ['--class' => 'UserSeeder']);

    $response = $this->post('/login', [
        'email' => 'admin@inventaris.test',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('seeded staff user can login', function () {
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    $this->artisan('db:seed', ['--class' => 'UserSeeder']);

    $response = $this->post('/login', [
        'email' => 'staff@inventaris.test',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('products.index'));
    $this->assertAuthenticated();
});

test('seeded manager user can login', function () {
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    $this->artisan('db:seed', ['--class' => 'UserSeeder']);

    $response = $this->post('/login', [
        'email' => 'manager@inventaris.test',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('category seeder creates initial categories', function () {
    $this->artisan('db:seed', ['--class' => 'CategorySeeder']);

    $this->assertDatabaseHas('categories', ['name' => 'Elektronik']);
    $this->assertDatabaseHas('categories', ['name' => 'Furniture']);
    $this->assertDatabaseHas('categories', ['name' => 'ATK']);
});
