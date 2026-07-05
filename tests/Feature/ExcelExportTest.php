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

function createExcelUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ── PRODUCT EXCEL EXPORT TESTS ──

test('admin, staff, and manager can export products list to Excel', function () {
    foreach (['Admin', 'Staff', 'Manager'] as $role) {
        $user = createExcelUserWithRole($role);

        $response = $this->actingAs($user)->get(route('products.export.excel'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=laporan-data-barang.xlsx');
    }
});

test('unauthorized users cannot export products list to Excel', function () {
    // Guest
    $response = $this->get(route('products.export.excel'));
    $response->assertRedirect(route('login'));

    // User with no role
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('products.export.excel'));
    $response->assertStatus(403);
});

test('product excel export respects search and category filters', function () {
    $admin = createExcelUserWithRole('Admin');

    // Export with category_id filter
    $response = $this->actingAs($admin)->get(route('products.export.excel', [
        'category_id' => $this->category->id,
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename=laporan-data-barang.xlsx');

    // Export with search filter
    $response = $this->actingAs($admin)->get(route('products.export.excel', [
        'search' => 'Projector',
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename=laporan-data-barang.xlsx');
});

// ── BORROWINGS EXCEL EXPORT TESTS ──

test('admin and manager can export borrowings history to Excel', function () {
    foreach (['Admin', 'Manager'] as $role) {
        $user = createExcelUserWithRole($role);

        $response = $this->actingAs($user)->get(route('borrowings.export.excel'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=laporan-riwayat-peminjaman.xlsx');
    }
});

test('staff cannot export borrowings history to Excel', function () {
    $staff = createExcelUserWithRole('Staff');

    $response = $this->actingAs($staff)->get(route('borrowings.export.excel'));
    $response->assertStatus(403);
});

test('unauthorized guest cannot export borrowings history to Excel', function () {
    $response = $this->get(route('borrowings.export.excel'));
    $response->assertRedirect(route('login'));
});

test('borrowing excel export respects search, status, and date range filters', function () {
    $admin = createExcelUserWithRole('Admin');

    $response = $this->actingAs($admin)->get(route('borrowings.export.excel', [
        'search' => 'Budi',
        'status' => 'borrowed',
        'start_date' => today()->subDays(5)->toDateString(),
        'end_date' => today()->toDateString(),
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename=laporan-riwayat-peminjaman.xlsx');
});
