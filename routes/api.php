<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BorrowingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin & Staff — full access (CRUD products, create borrowings, return borrowings)
    Route::middleware('role:Admin|Staff')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        Route::post('/borrowings', [BorrowingController::class, 'store']);
        Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnGoods']);
    });

    // Admin, Staff & Manager — read-only (list & view details)
    Route::middleware('role:Admin|Staff|Manager')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::get('/categories', [CategoryController::class, 'index']);

        Route::get('/borrowings', [BorrowingController::class, 'index']);
        Route::get('/borrowings/{borrowing}', [BorrowingController::class, 'show']);
    });
});
