<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);

    $this->category = Category::factory()->create(['name' => 'Elektronik']);
});

function createDashboardUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ── ROLE ACCESS TESTS ──

test('admin can access dashboard page', function () {
    $admin = createDashboardUserWithRole('Admin');

    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertStatus(200);
});

test('manager can access dashboard page', function () {
    $manager = createDashboardUserWithRole('Manager');

    $response = $this->actingAs($manager)->get(route('dashboard'));
    $response->assertStatus(200);
});

test('staff cannot access dashboard page directly', function () {
    $staff = createDashboardUserWithRole('Staff');

    $response = $this->actingAs($staff)->get(route('dashboard'));
    $response->assertStatus(403);
});

// ── REDIRECT ON LOGIN ──

test('staff logging in is redirected to products index instead of dashboard', function () {
    $staff = createDashboardUserWithRole('Staff');

    $response = $this->post(route('login'), [
        'email' => $staff->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('products.index'));
});

test('admin logging in is redirected to dashboard', function () {
    $admin = createDashboardUserWithRole('Admin');

    $response = $this->post(route('login'), [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
});

// ── STATISTICS CACHING ──

test('dashboard stats are cached and cleared on product change', function () {
    $admin = createDashboardUserWithRole('Admin');

    Product::factory()->create([
        'category_id' => $this->category->id,
        'stock' => 10,
        'condition' => 'good',
    ]);

    expect(Cache::has('dashboard_stats'))->toBeFalse();

    // Access dashboard to cache stats
    $this->actingAs($admin)->get(route('dashboard'));
    expect(Cache::has('dashboard_stats'))->toBeTrue();

    // Modify a product to check if cache is invalidated
    $product = Product::first();
    $product->update(['stock' => 8]);

    expect(Cache::has('dashboard_stats'))->toBeFalse();
});

// ── LOW STOCK NOTIFICATIONS ──

test('notifies admin and staff when product stock is at or below threshold', function () {
    $admin = createDashboardUserWithRole('Admin');
    $staff = createDashboardUserWithRole('Staff');
    $manager = createDashboardUserWithRole('Manager');

    // Create a product with stock above threshold (e.g. 10)
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'stock' => 10,
        'condition' => 'good',
    ]);

    // Clear notifications sent on creation
    $admin->unreadNotifications()->delete();
    $staff->unreadNotifications()->delete();
    $manager->unreadNotifications()->delete();

    // Trigger update stock to <= threshold
    $product->update(['stock' => 4]);

    // Admin & Staff must receive DB notifications
    expect($admin->unreadNotifications()->count())->toBe(1)
        ->and($staff->unreadNotifications()->count())->toBe(1);

    // Manager should not receive notifications
    expect($manager->unreadNotifications()->count())->toBe(0);

    $notif = $admin->unreadNotifications()->first();
    expect($notif->data['message'])->toContain($product->name)
        ->and($notif->data['stock'])->toBe(4);
});
