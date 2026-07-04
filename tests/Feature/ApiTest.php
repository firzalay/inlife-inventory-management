<?php

use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);

    $this->category = Category::factory()->create(['name' => 'Elektronik']);
    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Projector LG',
        'code' => 'LG-900',
        'stock_baik' => 10,
        'stock_rusak' => 2,
        'stock_perlu_perbaikan' => 1,
    ]);
});

function createApiUserWithRole(string $role): User
{
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);
    $user->assignRole($role);

    return $user;
}

// ── AUTHENTICATION TESTS ──

test('user can login via API with valid credentials and receive token', function () {
    $user = createApiUserWithRole('Staff');

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'user' => ['id', 'name', 'email', 'roles'],
            ],
        ]);
});

test('user login fails via API with invalid credentials', function () {
    $user = createApiUserWithRole('Staff');

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Kredensial login tidak cocok.',
        ]);
});

test('authenticated user can logout via API', function () {
    $user = createApiUserWithRole('Staff');
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);

    expect($user->tokens()->count())->toBe(0);
});

// ── PRODUCT API TESTS (ROLES CHECK) ──

test('manager can get products list and single product detail but cannot create', function () {
    $manager = createApiUserWithRole('Manager');
    $token = $manager->createToken('test-token')->plainTextToken;

    // Index
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/products');
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data', 'pagination']);

    // Show — should include split stock fields
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson("/api/products/{$this->product->id}");
    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Projector LG', 'stock_baik' => 10]);

    // Create (should fail with 403)
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/products', [
            'code' => 'NEW-CODE',
            'name' => 'Failed Product',
            'category_id' => $this->category->id,
            'stock_baik' => 5,
            'stock_rusak' => 0,
            'stock_perlu_perbaikan' => 0,
            'location' => 'Rak A',
        ]);
    $response->assertStatus(403);
});

test('staff can perform full CRUD operations on products via API', function () {
    $staff = createApiUserWithRole('Staff');
    $token = $staff->createToken('test-token')->plainTextToken;

    // Create
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/products', [
            'code' => 'NEW-99',
            'name' => 'Keyboard Logitech',
            'category_id' => $this->category->id,
            'stock_baik' => 15,
            'stock_rusak' => 0,
            'stock_perlu_perbaikan' => 0,
            'location' => 'Rak B',
        ]);
    $response->assertStatus(201);
    $productId = $response->json('data.id');

    // Update
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson("/api/products/{$productId}", [
            'code' => 'NEW-99',
            'name' => 'Keyboard Logitech Mechanical',
            'category_id' => $this->category->id,
            'stock_baik' => 12,
            'stock_rusak' => 1,
            'stock_perlu_perbaikan' => 0,
            'location' => 'Rak B',
        ]);
    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Keyboard Logitech Mechanical', 'stock_baik' => 12]);

    // Delete
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->deleteJson("/api/products/{$productId}");
    $response->assertStatus(200);

    $this->assertSoftDeleted('products', ['id' => $productId]);
});

// ── BORROWING API TESTS ──

test('staff can create borrowing transaction via API and deduct stock_baik', function () {
    $staff = createApiUserWithRole('Staff');
    $token = $staff->createToken('test-token')->plainTextToken;

    $postData = [
        'borrower_name' => 'Dewi Lestari',
        'borrow_date' => today()->toDateString(),
        'due_date' => today()->addDays(7)->toDateString(),
        'items' => [
            [
                'product_id' => $this->product->id,
                'quantity' => 4,
            ],
        ],
    ];

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/borrowings', $postData);

    $response->assertStatus(201)
        ->assertJsonFragment(['borrower_name' => 'Dewi Lestari']);

    // stock_baik deducted; others unchanged
    expect($this->product->refresh()->stock_baik)->toBe(6)
        ->and($this->product->refresh()->stock_rusak)->toBe(2)
        ->and($this->product->refresh()->stock_perlu_perbaikan)->toBe(1);
});

test('borrowing via API fails if stock_baik is insufficient', function () {
    $staff = createApiUserWithRole('Staff');
    $token = $staff->createToken('test-token')->plainTextToken;

    $postData = [
        'borrower_name' => 'Dewi Lestari',
        'borrow_date' => today()->toDateString(),
        'due_date' => today()->addDays(7)->toDateString(),
        'items' => [
            [
                'product_id' => $this->product->id,
                'quantity' => 15, // exceeds stock_baik of 10
            ],
        ],
    ];

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/borrowings', $postData);

    $response->assertStatus(422)
        ->assertJsonStructure(['errors' => ['items.0.quantity']]);

    expect($this->product->refresh()->stock_baik)->toBe(10);
});

test('staff can return items via API with Baik condition and restore stock_baik', function () {
    $staff = createApiUserWithRole('Staff');
    $token = $staff->createToken('test-token')->plainTextToken;

    $borrowing = Borrowing::factory()->create([
        'due_date' => today()->addDays(7),
        'status' => 'borrowed',
    ]);
    $detail = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->product->id,
        'quantity' => 3,
    ]);

    // Simulating stock_baik deduction beforehand
    $this->product->decrement('stock_baik', 3);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson("/api/borrowings/{$borrowing->id}/return", [
            'conditions' => [
                $detail->id => 'Baik',
            ],
        ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['status' => 'returned']);

    expect($this->product->refresh()->stock_baik)->toBe(10);
    expect($detail->refresh()->condition_on_return)->toBe('Baik');
});

test('staff can return items via API with Rusak condition which routes to stock_rusak', function () {
    $staff = createApiUserWithRole('Staff');
    $token = $staff->createToken('test-token')->plainTextToken;

    $borrowing = Borrowing::factory()->create([
        'due_date' => today()->addDays(7),
        'status' => 'borrowed',
    ]);
    $detail = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
    ]);

    $this->product->decrement('stock_baik', 2);
    $initialRusak = $this->product->fresh()->stock_rusak;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson("/api/borrowings/{$borrowing->id}/return", [
            'conditions' => [
                $detail->id => 'Rusak',
            ],
        ]);

    $response->assertStatus(200);
    // stock_baik must NOT be restored
    expect($this->product->refresh()->stock_baik)->toBe(8)
        ->and($this->product->refresh()->stock_rusak)->toBe($initialRusak + 2);
});

test('staff can return items via API with Hilang condition and no stock is restored', function () {
    $staff = createApiUserWithRole('Staff');
    $token = $staff->createToken('test-token')->plainTextToken;

    $borrowing = Borrowing::factory()->create([
        'due_date' => today()->addDays(7),
        'status' => 'borrowed',
    ]);
    $detail = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->product->id,
        'quantity' => 2,
    ]);

    $this->product->decrement('stock_baik', 2);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson("/api/borrowings/{$borrowing->id}/return", [
            'conditions' => [
                $detail->id => 'Hilang',
            ],
        ]);

    $response->assertStatus(200);
    // No stock column should increase
    $refreshed = $this->product->refresh();
    expect($refreshed->stock_baik)->toBe(8)
        ->and($refreshed->stock_rusak)->toBe(2)
        ->and($refreshed->stock_perlu_perbaikan)->toBe(1);
});
