<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup roles
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);

    // Setup Category
    $this->category = Category::factory()->create(['name' => 'Elektronik']);
});

function createProductUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ── ROLE ACCESS TESTS ──

test('admin can access product index and create page', function () {
    $admin = createProductUserWithRole('Admin');

    $response = $this->actingAs($admin)->get(route('products.index'));
    $response->assertStatus(200);

    $response = $this->actingAs($admin)->get(route('products.create'));
    $response->assertStatus(200);
});

test('staff can access product index and create page', function () {
    $staff = createProductUserWithRole('Staff');

    $response = $this->actingAs($staff)->get(route('products.index'));
    $response->assertStatus(200);

    $response = $this->actingAs($staff)->get(route('products.create'));
    $response->assertStatus(200);
});

test('manager can access product index but NOT create page', function () {
    $manager = createProductUserWithRole('Manager');

    $response = $this->actingAs($manager)->get(route('products.index'));
    $response->assertStatus(200);

    $response = $this->actingAs($manager)->get(route('products.create'));
    $response->assertStatus(403);
});

// ── CRUD OPERATIONS (ADMIN/STAFF) ──

test('admin can store a new product with image', function () {
    Storage::fake('public');
    $admin = createProductUserWithRole('Admin');
    $image = UploadedFile::fake()->image('laptop.jpg');

    $productData = [
        'code' => 'PRD-9999',
        'name' => 'Laptop ThinkPad',
        'category_id' => $this->category->id,
        'stock' => 15,
        'location' => 'Rak A1',
        'condition' => 'good',
        'image' => $image,
    ];

    $response = $this->actingAs($admin)->post(route('products.store'), $productData);

    $response->assertRedirect(route('products.index'));
    $this->assertDatabaseHas('products', [
        'code' => 'PRD-9999',
        'name' => 'Laptop ThinkPad',
        'stock' => 15,
        'location' => 'Rak A1',
        'condition' => 'good',
    ]);

    $product = Product::where('code', 'PRD-9999')->first();
    expect($product->image)->not->toBeNull();
    Storage::disk('public')->assertExists($product->image);
});

test('manager cannot store a new product', function () {
    $manager = createProductUserWithRole('Manager');

    $productData = [
        'code' => 'PRD-8888',
        'name' => 'Kamera Canon',
        'category_id' => $this->category->id,
        'stock' => 5,
        'location' => 'Rak B2',
        'condition' => 'good',
    ];

    $response = $this->actingAs($manager)->post(route('products.store'), $productData);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('products', ['code' => 'PRD-8888']);
});

test('staff can update product details and replace image', function () {
    Storage::fake('public');
    $staff = createProductUserWithRole('Staff');

    // Create initial product with image
    $oldImage = UploadedFile::fake()->image('old.jpg');
    $oldPath = $oldImage->store('products', 'public');
    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'image' => $oldPath,
    ]);

    $newImage = UploadedFile::fake()->image('new.jpg');
    $updateData = [
        'code' => 'PRD-UPDATED',
        'name' => 'Updated Name',
        'category_id' => $this->category->id,
        'stock' => 20,
        'location' => 'Rak C3',
        'condition' => 'damaged',
        'image' => $newImage,
    ];

    $response = $this->actingAs($staff)->patch(route('products.update', $product), $updateData);

    $response->assertRedirect(route('products.show', $product));

    $product->refresh();
    expect($product->code)->toBe('PRD-UPDATED')
        ->and($product->name)->toBe('Updated Name')
        ->and($product->stock)->toBe(20)
        ->and($product->condition)->toBe('damaged');

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($product->image);
});

test('manager cannot update product', function () {
    $manager = createProductUserWithRole('Manager');
    $product = Product::factory()->create(['category_id' => $this->category->id]);

    $response = $this->actingAs($manager)->patch(route('products.update', $product), [
        'code' => 'PRD-FORBIDDEN',
        'name' => 'Forbidden Edit',
        'category_id' => $this->category->id,
        'stock' => 10,
        'location' => 'Rak Z',
        'condition' => 'good',
    ]);

    $response->assertStatus(403);
    expect($product->refresh()->code)->not->toBe('PRD-FORBIDDEN');
});

test('staff can soft delete product', function () {
    $staff = createProductUserWithRole('Staff');
    $product = Product::factory()->create(['category_id' => $this->category->id]);

    $response = $this->actingAs($staff)->delete(route('products.destroy', $product));

    $response->assertRedirect(route('products.index'));
    $this->assertSoftDeleted($product);
});

test('manager cannot delete product', function () {
    $manager = createProductUserWithRole('Manager');
    $product = Product::factory()->create(['category_id' => $this->category->id]);

    $response = $this->actingAs($manager)->delete(route('products.destroy', $product));

    $response->assertStatus(403);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
});

// ── SEARCH & FILTER TESTS ──

test('can search products by name or code', function () {
    $user = createProductUserWithRole('Manager');
    Product::factory()->create(['name' => 'Kursi Kayu', 'code' => 'KRS-001', 'category_id' => $this->category->id]);
    Product::factory()->create(['name' => 'Meja Belajar', 'code' => 'MJA-002', 'category_id' => $this->category->id]);

    // Search by name
    $response = $this->actingAs($user)->get(route('products.index', ['search' => 'Kursi']));
    $response->assertSee('Kursi Kayu');
    $response->assertDontSee('Meja Belajar');

    // Search by code
    $response = $this->actingAs($user)->get(route('products.index', ['search' => 'MJA']));
    $response->assertSee('Meja Belajar');
    $response->assertDontSee('Kursi Kayu');
});

test('can filter products by category and condition', function () {
    $user = createProductUserWithRole('Manager');
    $otherCategory = Category::factory()->create(['name' => 'Furniture']);

    $p1 = Product::factory()->create(['name' => 'Laptop', 'category_id' => $this->category->id, 'condition' => 'good']);
    $p2 = Product::factory()->create(['name' => 'Kursi', 'category_id' => $otherCategory->id, 'condition' => 'damaged']);

    // Filter category
    $response = $this->actingAs($user)->get(route('products.index', ['category_id' => $this->category->id]));
    $response->assertSee('Laptop');
    $response->assertDontSee('Kursi');

    // Filter condition
    $response = $this->actingAs($user)->get(route('products.index', ['condition' => 'damaged']));
    $response->assertSee('Kursi');
    $response->assertDontSee('Laptop');
});
