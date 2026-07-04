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
    // Setup roles
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);

    // Setup Category & Products
    $this->category = Category::factory()->create(['name' => 'Elektronik']);
    $this->productA = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Proyektor Epson',
        'stock' => 10,
        'code' => 'PRD-001',
    ]);
    $this->productB = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Kabel HDMI',
        'stock' => 5,
        'code' => 'PRD-002',
    ]);
});

function createBorrowingUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ── ROLE ACCESS TESTS ──

test('admin can access borrowing list and create page', function () {
    $admin = createBorrowingUserWithRole('Admin');

    $response = $this->actingAs($admin)->get(route('borrowings.index'));
    $response->assertStatus(200);

    $response = $this->actingAs($admin)->get(route('borrowings.create'));
    $response->assertStatus(200);
});

test('staff can access borrowing list and create page', function () {
    $staff = createBorrowingUserWithRole('Staff');

    $response = $this->actingAs($staff)->get(route('borrowings.index'));
    $response->assertStatus(200);

    $response = $this->actingAs($staff)->get(route('borrowings.create'));
    $response->assertStatus(200);
});

test('manager can access borrowing list but NOT create page', function () {
    $manager = createBorrowingUserWithRole('Manager');

    $response = $this->actingAs($manager)->get(route('borrowings.index'));
    $response->assertStatus(200);

    $response = $this->actingAs($manager)->get(route('borrowings.create'));
    $response->assertStatus(403);
});

// ── TRANSACTION / STORAGE TESTS ──

test('staff can record a new borrowing transaction and deduct product stock', function () {
    $staff = createBorrowingUserWithRole('Staff');

    $postData = [
        'borrower_name' => 'Budi Santoso',
        'borrow_date' => today()->toDateString(),
        'due_date' => today()->addDays(5)->toDateString(),
        'items' => [
            [
                'product_id' => $this->productA->id,
                'quantity' => 3,
            ],
            [
                'product_id' => $this->productB->id,
                'quantity' => 2,
            ],
        ],
    ];

    $response = $this->actingAs($staff)->post(route('borrowings.store'), $postData);

    $response->assertRedirect(route('borrowings.index'));

    $this->assertDatabaseHas('borrowings', [
        'borrower_name' => 'Budi Santoso',
        'status' => 'borrowed',
    ]);

    $borrowing = Borrowing::where('borrower_name', 'Budi Santoso')->first();

    $this->assertDatabaseHas('borrowing_details', [
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->productA->id,
        'quantity' => 3,
    ]);

    $this->assertDatabaseHas('borrowing_details', [
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->productB->id,
        'quantity' => 2,
    ]);

    // Check stocks
    expect($this->productA->refresh()->stock)->toBe(7)
        ->and($this->productB->refresh()->stock)->toBe(3);
});

test('borrowing store fails if requested quantity exceeds product stock', function () {
    $staff = createBorrowingUserWithRole('Staff');

    $postData = [
        'borrower_name' => 'Budi Santoso',
        'borrow_date' => today()->toDateString(),
        'due_date' => today()->addDays(5)->toDateString(),
        'items' => [
            [
                'product_id' => $this->productA->id,
                'quantity' => 12, // exceeds stock of 10
            ],
        ],
    ];

    $response = $this->actingAs($staff)->post(route('borrowings.store'), $postData);

    $response->assertSessionHasErrors(['items.0.quantity']);
    expect($this->productA->refresh()->stock)->toBe(10); // unchanged
});

// ── RETURNS SYSTEM ──

test('staff can process returns, restore product stock, and record condition', function () {
    $staff = createBorrowingUserWithRole('Staff');

    // Create borrowing
    $borrowing = Borrowing::factory()->create([
        'due_date' => today()->addDays(5),
        'status' => 'borrowed',
    ]);
    $detailA = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->productA->id,
        'quantity' => 2,
    ]);
    $detailB = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->productB->id,
        'quantity' => 1,
    ]);

    // Adjust product stock simulating checkout deduction first
    $this->productA->decrement('stock', 2);
    $this->productB->decrement('stock', 1);

    $postData = [
        'conditions' => [
            $detailA->id => 'Baik',
            $detailB->id => 'Rusak',
        ],
    ];

    $response = $this->actingAs($staff)->post(route('borrowings.return', $borrowing), $postData);

    $response->assertRedirect(route('borrowings.show', $borrowing));

    $borrowing->refresh();
    expect($borrowing->status)->toBe('returned')
        ->and($borrowing->return_date->toDateString())->toBe(now()->toDateString());

    expect($detailA->refresh()->condition_on_return)->toBe('Baik')
        ->and($detailB->refresh()->condition_on_return)->toBe('Rusak');

    // Stock must be fully restored
    expect($this->productA->refresh()->stock)->toBe(10)
        ->and($this->productB->refresh()->stock)->toBe(5);
});

test('manager cannot process return of goods', function () {
    $manager = createBorrowingUserWithRole('Manager');
    $borrowing = Borrowing::factory()->create(['status' => 'borrowed']);

    $response = $this->actingAs($manager)->post(route('borrowings.return', $borrowing), [
        'conditions' => [],
    ]);

    $response->assertStatus(403);
});

// ── OVERDUE LOGIC ──

test('borrowing dynamic status calculations identifies overdue correctly', function () {
    // Borrowed and due_date in future
    $b1 = Borrowing::factory()->create([
        'due_date' => today()->addDays(2)->toDateString(),
        'status' => 'borrowed',
    ]);
    expect($b1->computed_status)->toBe('borrowed');

    // Borrowed and due_date in past
    $b2 = Borrowing::factory()->create([
        'due_date' => today()->subDays(2)->toDateString(),
        'status' => 'borrowed',
    ]);
    expect($b2->computed_status)->toBe('overdue');

    // Returned and due_date in past
    $b3 = Borrowing::factory()->create([
        'due_date' => today()->subDays(2)->toDateString(),
        'status' => 'returned',
    ]);
    expect($b3->computed_status)->toBe('returned');
});
