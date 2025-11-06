<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminBarangController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\RoleController;

Route::get('/', function () {
    return view('home');
});
Route::get('/home', function () {
    return view('home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::patch('/users/{user}/role', [RoleController::class, 'assignRole']);

Route::middleware(['auth', 'role:admin_barang'])
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
