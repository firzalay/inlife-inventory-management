<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Authenticated & verified routes available to all roles.
 */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
        Route::resource('products', ProductController::class)->only(['index', 'show']);
    });

/**
 * Admin-only routes — full access to all inventory management features.
 */
Route::middleware(['auth', 'verified', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');
});

/**
 * Staff routes — inventory access (categories, products, borrowings).
 */
Route::middleware(['auth', 'verified', 'role:Admin|Staff'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('index');
});

/**
 * Manager routes — read-only access to dashboard and reports.
 */
Route::middleware(['auth', 'verified', 'role:Admin|Manager'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('index');
});

require __DIR__.'/auth.php';
