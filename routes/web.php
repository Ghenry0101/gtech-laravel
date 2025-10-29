<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', function (Request $request) {
    $q = trim($request->query('q', ''));
    return view('search', ['q' => $q]);
})->name('search');
