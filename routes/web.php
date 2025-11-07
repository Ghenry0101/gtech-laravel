<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', function (Request $request) {
    $q = trim($request->query('q', ''));
    return view('search', ['q' => $q]);
})->name('search');

Route::get('/products/{category?}', [ProductController::class, 'index'])
    ->where('category', '^[a-z0-9-]+$')
    ->name('products.index');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');