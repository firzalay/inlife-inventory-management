<?php

use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Authenticated & verified dashboard route — accessible to Admin & Manager only.
 */
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:Admin|Manager'])
    ->name('dashboard');

/**
 * Notifications — mark as read route.
 */
Route::post('/notifications/{notification}/read', function (string $id) {
    auth()->user()->unreadNotifications->where('id', $id)->markAsRead();

    return back();
})->middleware(['auth'])->name('notifications.read');

/**
 * Profile management — available to all authenticated users.
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * Products — full CRUD access for Admin and Staff only.
 */
Route::middleware(['auth', 'verified', 'role:Admin|Staff'])
    ->group(function () {
        Route::resource('products', ProductController::class)->except(['index', 'show']);
    });

/**
 * Products — read-only access for Admin, Staff, and Manager.
 */
Route::middleware(['auth', 'verified', 'role:Admin|Staff|Manager'])
    ->group(function () {
        Route::get('products/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');
        Route::resource('products', ProductController::class)->only(['index', 'show']);
    });

/**
 * Borrowings — Admin & Staff write actions (create, store, return).
 */
Route::middleware(['auth', 'verified', 'role:Admin|Staff'])
    ->group(function () {
        Route::get('borrowings/create', [BorrowingController::class, 'create'])->name('borrowings.create');
        Route::post('borrowings', [BorrowingController::class, 'store'])->name('borrowings.store');
        Route::post('borrowings/{borrowing}/return', [BorrowingController::class, 'returnGoods'])->name('borrowings.return');
    });

/**
 * Borrowings — export pdf action for Admin & Manager.
 */
Route::middleware(['auth', 'verified', 'role:Admin|Manager'])
    ->group(function () {
        Route::get('borrowings/export/pdf', [BorrowingController::class, 'exportPdf'])->name('borrowings.export.pdf');
    });

/**
 * Borrowings — read-only list & details for all roles.
 */
Route::middleware(['auth', 'verified', 'role:Admin|Staff|Manager'])
    ->group(function () {
        Route::get('borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
        Route::get('borrowings/{borrowing}', [BorrowingController::class, 'show'])->name('borrowings.show');
    });

/**
 * Admin-only routes — full access to all inventory management features.
 */
Route::middleware(['auth', 'verified', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});

/**
 * Staff routes — inventory access (categories, products, borrowings).
 */
Route::middleware(['auth', 'verified', 'role:Admin|Staff'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', function () {
        return view('inventory.index');
    })->name('index');
});

/**
 * Manager routes — read-only access to dashboard and reports.
 */
Route::middleware(['auth', 'verified', 'role:Admin|Manager'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
});

require __DIR__.'/auth.php';
