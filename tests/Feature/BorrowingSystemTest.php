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

    // Setup Category & Products with new split stock columns
    $this->category = Category::factory()->create(['name' => 'Elektronik']);
    $this->productA = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Proyektor Epson',
        'stock_baik' => 10,
        'stock_rusak' => 0,
        'stock_perlu_perbaikan' => 0,
        'code' => 'PRD-001',
    ]);
    $this->productB = Product::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Kabel HDMI',
        'stock_baik' => 5,
        'stock_rusak' => 1,
        'stock_perlu_perbaikan' => 0,
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

test('staff can record a new borrowing transaction and deduct stock_baik', function () {
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

    // Only stock_baik is deducted; other columns unchanged
    expect($this->productA->refresh()->stock_baik)->toBe(7)
        ->and($this->productB->refresh()->stock_baik)->toBe(3);
});

test('borrowing store fails if requested quantity exceeds stock_baik', function () {
    $staff = createBorrowingUserWithRole('Staff');

    $postData = [
        'borrower_name' => 'Budi Santoso',
        'borrow_date' => today()->toDateString(),
        'due_date' => today()->addDays(5)->toDateString(),
        'items' => [
            [
                'product_id' => $this->productA->id,
                'quantity' => 12, // exceeds stock_baik of 10
            ],
        ],
    ];

    $response = $this->actingAs($staff)->post(route('borrowings.store'), $postData);

    $response->assertSessionHasErrors(['items.0.quantity']);
    expect($this->productA->refresh()->stock_baik)->toBe(10); // unchanged
});

test('borrowing fails when stock_baik is 0 even if rusak/perlu_perbaikan exist', function () {
    $staff = createBorrowingUserWithRole('Staff');

    $productNoBaik = Product::factory()->create([
        'category_id' => $this->category->id,
        'stock_baik' => 0,
        'stock_rusak' => 5,
        'stock_perlu_perbaikan' => 3,
    ]);

    $postData = [
        'borrower_name' => 'Test User',
        'borrow_date' => today()->toDateString(),
        'due_date' => today()->addDays(3)->toDateString(),
        'items' => [
            ['product_id' => $productNoBaik->id, 'quantity' => 1],
        ],
    ];

    $response = $this->actingAs($staff)->post(route('borrowings.store'), $postData);

    $response->assertSessionHasErrors(['items.0.quantity']);
    // stock_rusak and stock_perlu_perbaikan must remain untouched
    expect($productNoBaik->refresh()->stock_rusak)->toBe(5)
        ->and($productNoBaik->refresh()->stock_perlu_perbaikan)->toBe(3);
});

// ── RETURNS SYSTEM ──

test('returning product in good condition restores stock_baik', function () {
    $staff = createBorrowingUserWithRole('Staff');

    $borrowing = Borrowing::factory()->create([
        'due_date' => today()->addDays(5),
        'status' => 'borrowed',
    ]);
    $detail = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->productA->id,
        'quantity' => 2,
    ]);

    $this->productA->decrement('stock_baik', 2);

    $response = $this->actingAs($staff)->post(route('borrowings.return', $borrowing), [
        'conditions' => [$detail->id => 'Baik'],
    ]);

    $response->assertRedirect(route('borrowings.show', $borrowing));
    expect($this->productA->refresh()->stock_baik)->toBe(10); // fully restored
});

test('returning product as rusak adds to stock_rusak not stock_baik', function () {
    $staff = createBorrowingUserWithRole('Staff');

    $borrowing = Borrowing::factory()->create([
        'due_date' => today()->addDays(5),
        'status' => 'borrowed',
    ]);
    $detail = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->productA->id,
        'quantity' => 2,
    ]);

    $this->productA->decrement('stock_baik', 2);
    $initialRusak = $this->productA->fresh()->stock_rusak;

    $response = $this->actingAs($staff)->post(route('borrowings.return', $borrowing), [
        'conditions' => [$detail->id => 'Rusak'],
    ]);

    $response->assertRedirect(route('borrowings.show', $borrowing));
    // stock_baik must NOT be restored; stock_rusak must increase
    expect($this->productA->refresh()->stock_baik)->toBe(8)
        ->and($this->productA->refresh()->stock_rusak)->toBe($initialRusak + 2);
});

test('returning product as perlu perbaikan adds to stock_perlu_perbaikan', function () {
    $staff = createBorrowingUserWithRole('Staff');

    $borrowing = Borrowing::factory()->create([
        'due_date' => today()->addDays(5),
        'status' => 'borrowed',
    ]);
    $detail = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->productA->id,
        'quantity' => 1,
    ]);

    $this->productA->decrement('stock_baik', 1);
    $initialPerlu = $this->productA->fresh()->stock_perlu_perbaikan;

    $response = $this->actingAs($staff)->post(route('borrowings.return', $borrowing), [
        'conditions' => [$detail->id => 'Perlu Perbaikan'],
    ]);

    $response->assertRedirect(route('borrowings.show', $borrowing));
    expect($this->productA->refresh()->stock_baik)->toBe(9)
        ->and($this->productA->refresh()->stock_perlu_perbaikan)->toBe($initialPerlu + 1);
});

test('returning product as hilang does not restore any stock', function () {
    $staff = createBorrowingUserWithRole('Staff');

    $borrowing = Borrowing::factory()->create([
        'due_date' => today()->addDays(5),
        'status' => 'borrowed',
    ]);
    $detail = BorrowingDetail::factory()->create([
        'borrowing_id' => $borrowing->id,
        'product_id' => $this->productA->id,
        'quantity' => 3,
    ]);

    $this->productA->decrement('stock_baik', 3);

    $response = $this->actingAs($staff)->post(route('borrowings.return', $borrowing), [
        'conditions' => [$detail->id => 'Hilang'],
    ]);

    $response->assertRedirect(route('borrowings.show', $borrowing));
    // No stock should be restored for any column
    $refreshed = $this->productA->refresh();
    expect($refreshed->stock_baik)->toBe(7)
        ->and($refreshed->stock_rusak)->toBe(0)
        ->and($refreshed->stock_perlu_perbaikan)->toBe(0);
});

test('staff can process mixed condition returns', function () {
    $staff = createBorrowingUserWithRole('Staff');

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

    $this->productA->decrement('stock_baik', 2);
    $this->productB->decrement('stock_baik', 1);

    $response = $this->actingAs($staff)->post(route('borrowings.return', $borrowing), [
        'conditions' => [
            $detailA->id => 'Baik',
            $detailB->id => 'Rusak',
        ],
    ]);

    $response->assertRedirect(route('borrowings.show', $borrowing));

    $borrowing->refresh();
    expect($borrowing->status)->toBe('returned')
        ->and($borrowing->return_date->toDateString())->toBe(now()->toDateString());

    expect($detailA->refresh()->condition_on_return)->toBe('Baik')
        ->and($detailB->refresh()->condition_on_return)->toBe('Rusak');

    // ProductA Baik → restored to stock_baik
    expect($this->productA->refresh()->stock_baik)->toBe(10);
    // ProductB Rusak → goes to stock_rusak, not stock_baik
    expect($this->productB->refresh()->stock_baik)->toBe(4)
        ->and($this->productB->refresh()->stock_rusak)->toBe(2);
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
