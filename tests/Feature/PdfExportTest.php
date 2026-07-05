<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup roles
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);

    $this->category = Category::factory()->create(['name' => 'Elektronik']);
    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Projector LG',
        'code' => 'LG-900',
        'stock_baik' => 10,
    ]);
});

function createPdfUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ── PRODUCT PDF EXPORT TESTS ──

test('admin, staff, and manager can export products list to PDF', function () {
    foreach (['Admin', 'Staff', 'Manager'] as $role) {
        $user = createPdfUserWithRole($role);

        $response = $this->actingAs($user)->get(route('products.export.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
});

test('unauthorized users cannot export products list to PDF', function () {
    // Guest
    $response = $this->get(route('products.export.pdf'));
    $response->assertRedirect(route('login'));

    // User with no role
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('products.export.pdf'));
    $response->assertStatus(403);
});

test('product pdf export respects search and category filters', function () {
    $admin = createPdfUserWithRole('Admin');

    // Add another product in different category
    $otherCategory = Category::factory()->create(['name' => 'Furnitur']);
    $otherProduct = Product::factory()->create([
        'category_id' => $otherCategory->id,
        'name' => 'Kursi Kayu',
        'code' => 'KRS-100',
    ]);

    // Export with category_id filter
    $response = $this->actingAs($admin)->get(route('products.export.pdf', [
        'category_id' => $this->category->id,
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');

    // Export with search filter
    $response = $this->actingAs($admin)->get(route('products.export.pdf', [
        'search' => 'Projector',
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

// ── BORROWINGS PDF EXPORT TESTS ──

test('admin and manager can export borrowings history to PDF', function () {
    foreach (['Admin', 'Manager'] as $role) {
        $user = createPdfUserWithRole($role);

        $response = $this->actingAs($user)->get(route('borrowings.export.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
});

test('staff cannot export borrowings history to PDF', function () {
    $staff = createPdfUserWithRole('Staff');

    $response = $this->actingAs($staff)->get(route('borrowings.export.pdf'));
    $response->assertStatus(403);
});

test('unauthorized guest cannot export borrowings history to PDF', function () {
    $response = $this->get(route('borrowings.export.pdf'));
    $response->assertRedirect(route('login'));
});

test('borrowing pdf export respects search, status, and date range filters', function () {
    $admin = createPdfUserWithRole('Admin');

    $response = $this->actingAs($admin)->get(route('borrowings.export.pdf', [
        'search' => 'Budi',
        'status' => 'borrowed',
        'start_date' => today()->subDays(5)->toDateString(),
        'end_date' => today()->toDateString(),
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});
