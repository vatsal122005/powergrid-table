<?php

use App\Http\Controllers\ErrorController;
use App\Livewire\Product;
use App\Livewire\ProductEdit;
use App\Livewire\ProductForm;
use App\Livewire\ProductShow;
use App\Livewire\ProductTable;
use App\Http\Controllers\TaskErrorController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->prefix('products')->name('products.')->group(function () {
    Route::get('/create', ProductForm::class)->name('create');
    Route::get('/{id}/edit', ProductEdit::class)->name('edit');
    Route::get('/{id}', ProductShow::class)->name('show');
});

// Define the 'products' route name for the group root (for route('products'))
Route::middleware(['auth'])->get('products', Product::class)->name('products');

Route::get('/test-error',[ErrorController::class, 'trigger']);
