<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminBarangController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\ProfileAddressController;

Route::get('/', function () {
    return view('home');
});
Route::get('/home', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth:web', 'verified', 'profile.complete'])->name('dashboard');

Route::middleware(['auth:web', 'profile.complete'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/addresses', [ProfileAddressController::class, 'store'])->name('profile.addresses.store');
    Route::put('/profile/addresses/{address}', [ProfileAddressController::class, 'update'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{address}', [ProfileAddressController::class, 'destroy'])->name('profile.addresses.destroy');
});

Route::middleware(['auth:admin', 'role:product_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/barang', [AdminBarangController::class, 'index'])->name('barang.dashboard');
        Route::resource('barang/products', AdminProductController::class)
            ->names('barang.products')
            ->except(['show']);
        Route::resource('barang/categories', AdminCategoryController::class)
            ->names('barang.categories')
            ->except(['show']);
    });

require __DIR__.'/auth.php';
