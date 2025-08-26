<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('throttle:api')->group(function () {
        Route::apiResource('/task', TaskController::class)->only(['index', 'show']);
    });

    Route::middleware('throttle:api')->group(function () {
        Route::apiResource('/task', TaskController::class)->only(['store', 'update', 'destroy']);
    });

    Route::middleware('throttle:api')->group(function () {
        Route::apiResource('/categories', CategoryController::class);
    });
});
