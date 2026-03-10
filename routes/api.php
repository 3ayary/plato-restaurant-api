<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class,'register']);
Route::post('/logout', [AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('/login', [AuthController::class,'login']);

Route::get('/categories', [CategoryController::class,'getProducts']);
Route::post('/categories', [CategoryController::class,'createCategory']);
Route::put('/categories/{id}', [CategoryController::class,'update']);
Route::delete('/categories/{id}', [CategoryController::class,'destroy']);


