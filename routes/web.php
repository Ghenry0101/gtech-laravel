<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', function (Request $request) {
    $q = trim($request->query('q', ''));
    return view('search', ['q' => $q]);
})->name('search');

Route::get('/products/{category?}', [ProductController::class, 'index'])
    ->where('category', '^[a-z0-9-]+$')
    ->name('products.index');