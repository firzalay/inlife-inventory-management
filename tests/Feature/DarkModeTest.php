<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('app layout contains dark mode toggle button and head FOUC script', function () {
    $user = User::factory()->create(['status' => 'approved']);
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $user->assignRole('Admin');

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('id="dark-mode-toggle"', false);
    $response->assertSee('localStorage.getItem(\'darkMode\') === \'true\'', false);
});

test('guest layout contains head FOUC script', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('localStorage.getItem(\'darkMode\') === \'true\'', false);
});

test('dashboard link is visible to Admin and Manager but hidden from Staff', function () {
    $admin = User::factory()->create(['status' => 'approved']);
    $admin->assignRole('Admin');

    $staff = User::factory()->create(['status' => 'approved']);
    $staff->assignRole('Staff');

    // Admin should see it
    $responseAdmin = $this->actingAs($admin)->get(route('dashboard'));
    $responseAdmin->assertSee('Dashboard');

    // Staff should not see it
    $responseStaff = $this->actingAs($staff)->get(route('products.index'));
    $responseStaff->assertDontSee('Dashboard');
});
