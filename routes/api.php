<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'getProducts']);
Route::get('/products', [ProductController::class, 'index']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::post('/categories', [CategoryController::class, 'createCategory']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::put('/orders/{orderId}', [OrderController::class, 'statusUpdate']);
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orderId}', [OrderController::class, 'show']);
    Route::put('/orders/{orderId}', [OrderController::class, 'statusUpdate']);

});
