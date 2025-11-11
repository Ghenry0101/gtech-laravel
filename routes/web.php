<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;


Route::get('/product/{slug}', [ProductController::class, 'showBySlug'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('product.detail');

Route::get('/categories/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-\_]+')
    ->name('categories.show');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', function (Request $request) {
    $q = trim($request->query('q', ''));
    return view('search', ['q' => $q]);
})->name('search');

Route::get('/products/{category?}', [ProductController::class, 'index'])
    ->where('category', '^[a-z0-9-]+$')
    ->name('products.index');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

Route::get('/products/{slug}', [ProductController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-\_]+')
    ->name('products.show');

    Route::get('/reviews/create/{slug}', [ReviewController::class, 'create'])
    ->where('slug', '[A-Za-z0-9\-\_]+')
    ->name('reviews.create');

Route::post('/reviews', [ReviewController::class, 'store'])
    ->name('reviews.store');